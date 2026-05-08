<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\Course;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $tickets = SupportTicket::query()
            ->with(['user', 'course.teacher', 'assignedTeacher'])
            ->visibleTo($user)
            ->latest()
            ->get();

        return view('support-tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        $courses = $this->getAvailableCourses();

        return view('support-tickets.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $courses = $this->getAvailableCourses();

        $validated = $request->validate([
            'type' => ['required', Rule::in(SupportTicket::typeOptions())],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ]);

        $course = null;

        if ($validated['course_id'] ?? null) {
            $course = $courses->firstWhere('id', (int) $validated['course_id']);

            if (!$course) {
                abort(403);
            }
        }

        if ($validated['type'] !== SupportTicket::TYPE_TECHNICAL_ISSUE && !$course) {
            return back()
                ->withErrors(['course_id' => 'Выберите курс для этого типа обращения.'])
                ->withInput();
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'course_id' => $course?->id,
            'assigned_teacher_id' => $course?->teacher_id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => SupportTicket::STATUS_NEW,
        ]);

        $this->notifyAboutNewTicket($ticket);

        ActionLogger::log(
            'Создание заявки',
            'Создана заявка: ' . $ticket->subject,
            $request
        );

        return redirect()
            ->route('support-tickets.show', $ticket)
            ->with('success', 'Заявка создана');
    }

    public function show(SupportTicket $supportTicket): View
    {
        $this->authorizeView($supportTicket);

        $supportTicket->load(['user', 'course.teacher', 'assignedTeacher']);

        return view('support-tickets.show', [
            'ticket' => $supportTicket,
            'canManageTicket' => $this->canManage($supportTicket),
        ]);
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        if (!$this->canManage($supportTicket)) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(SupportTicket::statusOptions())],
        ]);

        $oldStatus = $supportTicket->status;

        $supportTicket->update([
            'status' => $validated['status'],
        ]);

        $this->notifyAboutStatusChange($supportTicket);

        ActionLogger::log(
            'Обновление статуса заявки',
            'Статус заявки "' . $supportTicket->subject . '" изменён: ' . $oldStatus . ' -> ' . $supportTicket->status,
            $request
        );

        return redirect()
            ->route('support-tickets.show', $supportTicket)
            ->with('success', 'Статус заявки обновлён');
    }

    private function getAvailableCourses(): Collection
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return Course::with('teacher')
                ->orderBy('title')
                ->get();
        }

        if ($user->role === 'teacher') {
            return Course::with('teacher')
                ->where('teacher_id', $user->id)
                ->orderBy('title')
                ->get();
        }

        return $user->courses()
            ->with('teacher')
            ->orderBy('title')
            ->get();
    }

    private function authorizeView(SupportTicket $supportTicket): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($supportTicket->user_id === $user->id) {
            return;
        }

        if ($user->role === 'teacher' && $supportTicket->assigned_teacher_id === $user->id) {
            return;
        }

        abort(403);
    }

    private function canManage(SupportTicket $supportTicket): bool
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'teacher'
            && $supportTicket->assigned_teacher_id === $user->id;
    }

    private function notifyAboutNewTicket(SupportTicket $ticket): void
    {
        $ticket->loadMissing(['user', 'course', 'assignedTeacher']);

        if ($ticket->type === SupportTicket::TYPE_TECHNICAL_ISSUE) {
            $admins = User::where('role', 'admin')
                ->where('id', '!=', $ticket->user_id)
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Новая техническая заявка',
                    'body' => 'Пользователь ' . $ticket->user->name . ' создал заявку: ' . $ticket->subject,
                    'type' => 'system',
                    'action_url' => route('support-tickets.show', $ticket),
                    'is_read' => false,
                ]);
            }

            return;
        }

        if ($ticket->assigned_teacher_id && $ticket->assigned_teacher_id !== $ticket->user_id) {
            Notification::create([
                'user_id' => $ticket->assigned_teacher_id,
                'title' => 'Новая заявка по курсу',
                'body' => 'Пользователь ' . $ticket->user->name . ' создал заявку: ' . $ticket->subject,
                'type' => 'system',
                'action_url' => route('support-tickets.show', $ticket),
                'is_read' => false,
            ]);
        }
    }

    private function notifyAboutStatusChange(SupportTicket $ticket): void
    {
        if ($ticket->user_id === Auth::id()) {
            return;
        }

        Notification::create([
            'user_id' => $ticket->user_id,
            'title' => 'Статус заявки обновлён',
            'body' => 'Заявка "' . $ticket->subject . '" переведена в статус: ' . $ticket->status,
            'type' => 'system',
            'action_url' => route('support-tickets.show', $ticket),
            'is_read' => false,
        ]);
    }
}
