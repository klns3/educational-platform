<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;

        $oldName = $user->name;
        $oldEmail = $user->email;
        $avatarChanged = $request->hasFile('avatar');

        $user->fill($request->validated());

        if ($avatarChanged) {
            $request->validate([
                'avatar' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($user->avatar) {
                $oldAvatar = $user->avatar;
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');

            $user->avatar = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $changes = [];

        if ($oldName !== $user->name) {
            $changes[] = 'имя: ' . $oldName . ' → ' . $user->name;
        }

        if ($oldEmail !== $user->email) {
            $changes[] = 'email: ' . $oldEmail . ' → ' . $user->email;
        }

        if ($avatarChanged) {
            $changes[] = 'обновлён аватар';
        }

        $user->save();

        if ($avatarChanged) {
            \App\Models\User::forgetAvatarUrlCache($oldAvatar);
            \App\Models\User::forgetAvatarUrlCache($user->avatar);
        }

        if (!empty($changes)) {
            ActionLogger::log(
                'Обновление профиля',
                'Пользователь обновил профиль: ' . implode('; ', $changes),
                $request
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $userName = $user->name;
        $userEmail = $user->email;
        $userId = $user->id;
        $avatarPath = $user->avatar;

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            \App\Models\User::forgetAvatarUrlCache($avatarPath);
        }

        ActionLogger::log(
            'Удаление аккаунта',
            'Пользователь удалил аккаунт: ' . $userName . ' (' . $userEmail . '), ID: ' . $userId,
            $request
        );

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
