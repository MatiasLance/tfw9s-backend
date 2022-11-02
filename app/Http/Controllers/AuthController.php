<?php

namespace App\Http\Controllers;

use App\Mail\SendPasswordResetLink;
use App\Modules\Auth\AuthServiceInterface;
use App\Modules\Http\Message;
use App\Modules\Support\Traits\HandlesValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HandlesValidation;

    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Modules\Http\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request, Message $message)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
        } catch (ValidationException $e) {
            return $this->handleValidationError($e);
        }

        $exposedAttributes = [
            'id',
            'first_name',
            'last_name',
            'email',
        ];

        $message->setTitle("Email or password is not found");
        $message->setStatus(200);

        $response = [
            'isLoggedIn' => false,
            'user' => null,
            'session' => (object)[
                'lifetime' => intval(env('SESSION_LIFETIME', 120))
            ]
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $message->setTitle("User successfully logged in");
            $response['isLoggedIn'] = true;
            $response['user'] = $request->user()->only($exposedAttributes);
        }

        $message->setData($response);

        return $message->render();
    }

    /**
     * Handle password reset requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Modules\Http\Message  $message
     * 
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request, Message $message)
    {
        $email = $request->input('email');

        $this->authService->forgotPassword($email);

        $message->setContent(200, 'Reset request processed', 'If we find the email in our database, you will receive the link via email to reset your password');
        
        return $message->render();
    }

    /**
     * Reset password request due to forgotten password
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Modules\Http\Message  $message
     * 
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request, Message $message)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');
        $token = $request->input('token');

        $isSuccess = $this->authService->resetPassword($email, $password, $passwordConfirmation, $token);
        
        if ($isSuccess) {
            $message->setContent(200, 'Password reset sucessfully');
        } else {
            $message->setContent(400, 'Password reset failed');
        }

        return $message->render();
    }

    /**
     * Invalidate the user's session
     * 
     * @param Request $request
     * @param Message $message
     * 
     * @return Response
     */
    public function logout(Request $request, Message $message)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message->setContent(200, 'User logged out successfully');
        return $message->render();
    }
}