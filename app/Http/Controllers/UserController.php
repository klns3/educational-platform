<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('classGroup')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                if ($request->role === 'pending') {
                    $query->whereNull('role');
                } else {
                    $query->where('role', $request->role);
                }
            })
            ->when($request->filled('class_group_id'), function ($query) use ($request) {
                $query->where('class_group_id', $request->class_group_id);
            })
            ->orderByRaw('CASE WHEN role IS NULL THEN 0 ELSE 1 END')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $groups = ClassGroup::orderBy('name')->get();
        $pendingUsersCount = User::whereNull('role')->count();

        return view('users.index', compact('users', 'groups', 'pendingUsersCount'));
    }

    public function edit(User $user): View
    {
        $groups = ClassGroup::orderBy('name')->get();

        return view('users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['nullable', 'in:admin,teacher,student'],
            'class_group_id' => ['nullable', 'integer', 'exists:class_groups,id'],
        ]);

        $oldRole = $user->role ?? 'нет роли';
        $oldGroup = $user->classGroup?->name ?? 'Без группы';

        $role = $request->role ?: null;

        $classGroupId = $role === 'student'
            ? $request->class_group_id
            : null;

        $user->update([
            'role' => $role,
            'class_group_id' => $classGroupId,
        ]);

        $user->load('classGroup');

        ActionLogger::log(
            'Обновление пользователя',
            'Изменён пользователь: ' . $user->name .
            '. Роль: ' . $oldRole . ' → ' . ($user->role ?? 'нет роли') .
            '. Группа: ' . $oldGroup . ' → ' . ($user->classGroup?->name ?? 'Без группы'),
            $request
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Пользователь обновлён');
    }
}