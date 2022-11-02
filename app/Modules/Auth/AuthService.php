<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Exceptions\RequestThrottledException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    public function forgotPassword(string $email)
    {
        $status = Password::sendResetLink(
            [
                'email' => $email,
            ]
        );

        if ($status === Password::RESET_THROTTLED) {
            throw new RequestThrottledException('Password reset request throttled. Too many requests');
        }

        return true;
    }

    public function resetPassword(string $email, string $password, string $passwordConfirmation, string $token)
    {
        $credentials = [
            'token' => $token,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];
        $status = Password::reset($credentials, function($user, $password) {
            $user
                ->forceFill([
                    'password' => Hash::make($password)
                ])
                ->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET;
    }
}