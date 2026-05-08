<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassGroupController extends Controller
{
    public function index(): View
    {
        $groups = ClassGroup::withCount([
                'users as students_count' => function ($query) {
                    $query->where('role', 'student');
                },
            ])
            ->latest()
            ->get();

        return view('class-groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('class-groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:class_groups,name'],
            'description' => ['nullable', 'string'],
        ]);

        $group = ClassGroup::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        ActionLogger::log(
            'Создание группы',
            'Создана группа: ' . $group->name,
            $request
        );

        return redirect()
            ->route('class-groups.index')
            ->with('success', 'Группа создана');
    }

    public function edit(ClassGroup $classGroup): View
    {
        $classGroup->load(['users' => function ($query) {
            $query->where('role', 'student')->orderBy('name');
        }]);

        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get();

        return view('class-groups.edit', compact('classGroup', 'students'));
    }

    public function update(Request $request, ClassGroup $classGroup): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:class_groups,name,' . $classGroup->id],
            'description' => ['nullable', 'string'],
            'students' => ['nullable', 'array'],
            'students.*' => ['integer', 'exists:users,id'],
        ]);

        $oldName = $classGroup->name;

        $classGroup->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        User::where('class_group_id', $classGroup->id)
            ->where('role', 'student')
            ->update(['class_group_id' => null]);

        User::where('class_group_id', $classGroup->id)
            ->where('role', '!=', 'student')
            ->update(['class_group_id' => null]);

        if ($request->filled('students')) {
            User::whereIn('id', $request->students)
                ->where('role', 'student')
                ->update(['class_group_id' => $classGroup->id]);
        }

        ActionLogger::log(
            'Обновление группы',
            'Обновлена группа: ' . $oldName . ' → ' . $classGroup->name,
            $request
        );

        return redirect()
            ->route('class-groups.index')
            ->with('success', 'Группа обновлена');
    }

    public function destroy(ClassGroup $classGroup): RedirectResponse
    {
        $name = $classGroup->name;

        User::where('class_group_id', $classGroup->id)
            ->update(['class_group_id' => null]);

        $classGroup->delete();

        ActionLogger::log(
            'Удаление группы',
            'Удалена группа: ' . $name,
            request()
        );

        return redirect()
            ->route('class-groups.index')
            ->with('success', 'Группа удалена');
    }
}