<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\ClassGroup;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::user()
            ->notifications()
            ->where(function ($query) {
                $query->whereNull('type')
                    ->orWhere('type', 'system');
            })
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => Auth::user()
                ->notifications()
                ->where('is_read', false)
                ->where(function ($query) {
                    $query->whereNull('type')
                        ->orWhere('type', 'system');
                })
                ->count(),
        ]);
    }

    public function broadcastCreate(): View
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $user = Auth::user();

        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get();

        $groups = ClassGroup::orderBy('name')->get();

        return view('notifications.broadcast', compact('user', 'students', 'groups'));
    }

    public function broadcastStore(Request $request): RedirectResponse
    {
        if (!in_array(Auth::user()->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $user = Auth::user();

        $request->validate([
            'target_type' => ['required', 'in:all,students,teachers,admins,group,user'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'class_group_id' => ['nullable', 'integer', 'exists:class_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $targetType = $request->target_type;

        if ($user->role === 'teacher' && !in_array($targetType, ['students', 'group', 'user'])) {
            abort(403);
        }

        $recipients = User::query();

        if ($targetType === 'students') {
            $recipients->where('role', 'student');
        }

        if ($targetType === 'teachers') {
            $recipients->where('role', 'teacher');
        }

        if ($targetType === 'admins') {
            $recipients->where('role', 'admin');
        }

        if ($targetType === 'group') {
            $request->validate([
                'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            ]);

            $recipients->where('role', 'student')
                ->where('class_group_id', $request->class_group_id);
        }

        if ($targetType === 'user') {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);

            $selectedUser = User::findOrFail($request->user_id);

            if ($user->role === 'teacher' && $selectedUser->role !== 'student') {
                abort(403);
            }

            $recipients->where('id', $selectedUser->id);
        }

        $users = $recipients->get();

        foreach ($users as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'title' => $request->title,
                'body' => $request->body,
                'type' => 'system',
                'is_read' => false,
            ]);
        }

        ActionLogger::log(
            'Рассылка уведомлений',
            'Отправлена рассылка "' . $request->title . '". Получателей: ' . $users->count(),
            $request
        );

        return redirect()
            ->route('notifications.broadcast.create')
            ->with('success', 'Уведомление отправлено. Получателей: ' . $users->count());
    }

    public function read(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
        ]);

        return back();
    }

    public function readAll(): RedirectResponse
    {
        Auth::user()
            ->notifications()
            ->where('is_read', false)
            ->where(function ($query) {
                $query->whereNull('type')
                    ->orWhere('type', 'system');
            })
            ->update(['is_read' => true]);

        return back()->with('success', 'Все уведомления прочитаны');
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $title = $notification->title;

        $notification->delete();

        ActionLogger::log(
            'Удаление уведомления',
            'Удалено уведомление: ' . $title,
            request()
        );

        return back()->with('success', 'Уведомление удалено');
    }
}
