<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->validate(['email' => ['required','email']]);

        $status = Password::sendResetLink($request->only('email'));

        $audit->log('auth.password_reset_link', null, null, ['email' => strtolower($request->input('email'))], $request);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function reset(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required','email'],
            'password' => ['required','string','min:10','confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request, $audit) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                $audit->log('auth.password_reset_done', 'users', (string) $user->id, [], $request, $user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
