<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\InvitationCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invite_code' => ['nullable', 'string', 'max:255'],
        ]);

        $inviteCodeValue = trim((string) $request->invite_code);
        $invitationCode = null;

        if ($inviteCodeValue !== '') {
            $invitationCode = InvitationCode::where('code', mb_strtoupper($inviteCodeValue))
                ->where('is_active', true)
                ->first();

            if (!$invitationCode) {
                return back()
                    ->withErrors(['invite_code' => 'Неверный или отключённый пригласительный код.'])
                    ->withInput();
            }

            if ($invitationCode->role === InvitationCode::ROLE_STUDENT && !$invitationCode->class_group_id) {
                return back()
                    ->withErrors(['invite_code' => 'Этот код ученика не привязан к группе.'])
                    ->withInput();
            }
        }

        $user = DB::transaction(function () use ($request, $invitationCode) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $invitationCode?->role,
                'class_group_id' => $invitationCode?->role === InvitationCode::ROLE_STUDENT
                    ? $invitationCode->class_group_id
                    : null,
            ]);

            if ($invitationCode) {
                $invitationCode->increment('uses_count');
                $invitationCode->update([
                    'last_used_at' => now(),
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return $user->role === null
            ? redirect()->route('pending.role')
            : redirect()->route('dashboard');
    }
}
