<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $users = $this->getUsers($request->search);

        return view('messages.index', [
            'users' => $users,
            'activeUser' => null,
            'messages' => collect(),
            'search' => $request->search,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->getUnreadMessagesCount(),
        ]);
    }

    public function chat(Request $request, User $user): View
    {
        if ($user->id === Auth::id()) {
            abort(403);
        }

        $users = $this->getUsers($request->search);

        $messages = $this->getConversation($user);

        $this->markAsRead($user);

        return view('messages.index', [
            'users' => $users,
            'activeUser' => $user,
            'messages' => $messages,
            'search' => $request->search,
        ]);
    }

    public function fetchMessages(User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            abort(403);
        }

        $this->markAsRead($user);

        $messages = $this->getConversation($user)->map(function ($message) {
            return [
                'id' => $message->id,
                'body' => e($message->body),
                'sender_id' => $message->sender_id,
                'recipient_id' => $message->recipient_id,
                'is_mine' => $message->sender_id === Auth::id(),
                'is_read' => (bool) $message->is_read,
                'created_at' => $message->created_at->format('d.m.Y H:i'),
                'delete_url' => route('messages.destroy', $message),
            ];
        });

        return response()->json([
            'messages' => $messages,
            'is_typing' => $this->isUserTyping($user),
        ]);
    }

    public function updateTypingStatus(Request $request, User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'is_typing' => ['required', 'boolean'],
        ]);

        $cacheKey = $this->typingCacheKey(Auth::id(), $user->id);

        if ($validated['is_typing']) {
            Cache::put($cacheKey, true, now()->addSeconds(5));
        } else {
            Cache::forget($cacheKey);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function send(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === Auth::id()) {
            abort(403);
        }

        $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $user->id,
            'body' => trim($request->body),
            'is_read' => false,
        ]);

        Cache::forget($this->typingCacheKey(Auth::id(), $user->id));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'body' => e($message->body),
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'is_mine' => true,
                    'is_read' => false,
                    'created_at' => $message->created_at->format('d.m.Y H:i'),
                    'delete_url' => route('messages.destroy', $message),
                ],
            ]);
        }

        return redirect()->route('messages.chat', $user);
    }

    public function destroy(Request $request, Message $message): JsonResponse|RedirectResponse
    {
        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        $recipientId = $message->recipient_id;

        $message->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()
            ->route('messages.chat', $recipientId)
            ->with('success', 'Сообщение удалено');
    }

    private function getConversation(User $user)
    {
        return Message::with(['sender', 'recipient'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', Auth::id())
                    ->where('recipient_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->where('recipient_id', Auth::id());
            })
            ->orderBy('created_at')
            ->get();
    }

    private function markAsRead(User $user): void
    {
        Message::where('sender_id', $user->id)
            ->where('recipient_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    private function getUsers(?string $search = null)
    {
        return User::where('id', '!=', Auth::id())
            ->whereNotNull('role')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('role', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("
                (
                    SELECT MAX(created_at)
                    FROM messages
                    WHERE 
                        (messages.sender_id = users.id AND messages.recipient_id = ?)
                        OR
                        (messages.sender_id = ? AND messages.recipient_id = users.id)
                ) DESC
            ", [Auth::id(), Auth::id()])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $user->unread_messages_count = Message::where('sender_id', $user->id)
                    ->where('recipient_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                $user->last_message = Message::where(function ($q) use ($user) {
                    $q->where('sender_id', Auth::id())
                        ->where('recipient_id', $user->id);
                })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('sender_id', $user->id)
                            ->where('recipient_id', Auth::id());
                    })
                    ->latest()
                    ->first();

                return $user;
            });
    }

    private function isUserTyping(User $user): bool
    {
        return Cache::has($this->typingCacheKey($user->id, Auth::id()));
    }

    private function typingCacheKey(int $senderId, int $recipientId): string
    {
        return "chat_typing:{$senderId}:{$recipientId}";
    }

    private function getUnreadMessagesCount(): int
    {
        return Message::where('recipient_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
}
