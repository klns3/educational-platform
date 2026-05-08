<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\ClassGroup;
use App\Models\InvitationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvitationCodeController extends Controller
{
    public function index(): View
    {
        $codes = InvitationCode::with(['classGroup', 'creator'])
            ->latest()
            ->get();

        $groups = ClassGroup::orderBy('name')->get();

        return view('invitation-codes.index', compact('codes', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'role' => ['required', Rule::in(InvitationCode::roleOptions())],
                'class_group_id' => [
                    Rule::requiredIf(fn () => $request->input('role') === InvitationCode::ROLE_STUDENT),
                    'nullable',
                    'integer',
                    'exists:class_groups,id',
                ],
            ],
            [
                'class_group_id.required' => 'Для кода ученика нужно выбрать группу.',
            ]
        );

        $invitationCode = InvitationCode::create([
            'code' => $this->generateUniqueCode(),
            'role' => $validated['role'],
            'class_group_id' => $validated['role'] === InvitationCode::ROLE_STUDENT
                ? $validated['class_group_id']
                : null,
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        ActionLogger::log(
            'Создание пригласительного кода',
            'Создан пригласительный код: ' . $invitationCode->code,
            $request
        );

        return redirect()
            ->route('invitation-codes.index')
            ->with('success', 'Пригласительный код создан: ' . $invitationCode->code);
    }

    public function toggle(InvitationCode $invitationCode): RedirectResponse
    {
        $invitationCode->update([
            'is_active' => !$invitationCode->is_active,
        ]);

        ActionLogger::log(
            'Изменение статуса пригласительного кода',
            'Код ' . $invitationCode->code . ' теперь ' . ($invitationCode->is_active ? 'активен' : 'отключён'),
            request()
        );

        return back()->with('success', 'Статус кода обновлён');
    }

    public function destroy(InvitationCode $invitationCode): RedirectResponse
    {
        $code = $invitationCode->code;
        $invitationCode->delete();

        ActionLogger::log(
            'Удаление пригласительного кода',
            'Удалён пригласительный код: ' . $code,
            request()
        );

        return back()->with('success', 'Пригласительный код удалён');
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (InvitationCode::where('code', $code)->exists());

        return $code;
    }
}
