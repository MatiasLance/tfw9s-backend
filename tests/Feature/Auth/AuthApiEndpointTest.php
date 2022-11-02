<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Auth\AuthServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthApiEndpointtest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test logging through sanctum as SPA
     *
     * @return void
     */
    public function test_sanctum_spa_login_success()
    {
        $userData = [
            'email' => "testSeederUser@example.com"
        ];
        $user = User::factory()->create($userData);

        $initialResponse = $this->get('/sanctum/csrf-cookie');
        
        $initialResponse->assertStatus(204);

        $xsrfToken = $initialResponse->getCookie('XSRF-TOKEN');
        $session = $initialResponse->getCookie('livechat_session');

        $loginAttemptResponse = $this
                                    ->withHeaders([
                                        'X-XSRF-TOKEN' => $xsrfToken,
                                    ])
                                    ->withCookies([
                                        'XSRF-TOKEN' => $xsrfToken,
                                        'livechat_session' => $session,
                                    ])
                                    ->post('/api/v1/auth/login', [
                                        'email' => $user->email,
                                        'password' => 'password', //default password from factory
                                    ]);

        $loginAttemptResponse
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'isLoggedIn' => true,
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                    ],
                    'session' => [
                        'lifetime' => intval(env('SESSION_LIFETIME', 120)),
                    ]
                ]
            ]);
    }

    /**
     * Test loggin in through Sanctum as SPA but account is not found
     * 
     * @return void
     */
    public function test_sanctum_spa_login_account_not_found()
    {
        $userData = [
            'email' => "testSeederUser@example.com"
        ];
        $user = User::factory()->create($userData);

        $initialResponse = $this->get('/sanctum/csrf-cookie');
        
        $initialResponse->assertStatus(204);

        $xsrfToken = $initialResponse->getCookie('XSRF-TOKEN');
        $session = $initialResponse->getCookie('livechat_session');

        $loginAttemptResponse = $this
                                    ->withHeaders([
                                        'X-XSRF-TOKEN' => $xsrfToken,
                                    ])
                                    ->withCookies([
                                        'XSRF-TOKEN' => $xsrfToken,
                                        'livechat_session' => $session,
                                    ])
                                    ->post('/api/v1/auth/login', [
                                        'email' => $user->email,
                                        'password' => 'incorrectPassword', //default password from factory
                                    ]);

        $loginAttemptResponse
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'isLoggedIn' => false,
                    'user' => null,
                    'session' => [
                        'lifetime' => intval(env('SESSION_LIFETIME', 120)),
                    ]
                ]
            ]);

            
        $listResponse = $this->get('api/v1/users/me');
        $listResponse->assertStatus(302);
    }

    /**
     * Test logging through Sanctum but it fails due to invalid input
     * 
     * @return void
     */
    public function test_sanctum_spa_login_invalid_input()
    {
        $initialResponse = $this->get('/sanctum/csrf-cookie');
        
        $initialResponse->assertStatus(204);

        $xsrfToken = $initialResponse->getCookie('XSRF-TOKEN');
        $session = $initialResponse->getCookie('livechat_session');

        $loginAttemptResponse = $this
                                    ->withHeaders([
                                        'X-XSRF-TOKEN' => $xsrfToken,
                                    ])
                                    ->withCookies([
                                        'XSRF-TOKEN' => $xsrfToken,
                                        'livechat_session' => $session,
                                    ])
                                    ->post('/api/v1/auth/login', [
                                        'email' => 'not-a-email-address',
                                        'password' => 'incorrectPassword',
                                    ]);
                                    
        $loginAttemptResponse
            ->assertStatus(400)
            ->assertJson([
                'title' => 'The given data was invalid',
                'status' => 400,
                'errorCode' => 'Err:user_input_invalid',
                'data' => [
                    'errors' => []
                ]
            ]);

        $listResponse = $this->get('api/v1/users/me');
        $listResponse->assertStatus(302);
    }

    /**
     * Test logging out
     * 
     * @return void
     */
    public function test_logout_sanctum()
    {
        $userData = [
            'email' => "testSeederUser@example.com"
        ];
        $user = User::factory()->create($userData);

        $initialResponse = $this->get('/sanctum/csrf-cookie');
        
        $initialResponse->assertStatus(204);

        $xsrfToken = $initialResponse->getCookie('XSRF-TOKEN');
        $session = $initialResponse->getCookie('livechat_session');

        $loginAttemptResponse = $this
                                    ->withHeaders([
                                        'X-XSRF-TOKEN' => $xsrfToken,
                                    ])
                                    ->withCookies([
                                        'XSRF-TOKEN' => $xsrfToken,
                                        'livechat_session' => $session,
                                    ])
                                    ->post('/api/v1/auth/login', [
                                        'email' => $user->email,
                                        'password' => 'password', //default password from factory
                                    ]);

        // Check if logged in
        $listResponse = $this->get('api/v1/users/me');
        $listResponse->assertStatus(200);

        $logoutResponse = $this->post('api/v1/auth/logout');
        $logoutResponse
            ->assertStatus(200)
            ->assertJson([
                'title' => 'User logged out successfully',
                'status' => 200,
            ]);

        $this->resetAuth();

        // Check if logged in
        $listResponse = $this->get('api/v1/users/me');
        $listResponse->assertStatus(302);
    }

    public function testForgotPassword()
    {
        $email = 'test@example.com';

        User::factory()
            ->create([
                'email' => $email,
            ]);

        $this->mock(AuthServiceInterface::class, function(MockInterface $mock) {
            $mock->shouldReceive('forgotPassword')->once();
        });

        $response = $this->post('/api/v1/auth/forgot-password', [
            'email' => $email,
        ]);
        
        $response->assertStatus(200);
    }

    public function testResetPassword()
    {
        $oldPassword = 'oldPassword';
        $newPassword = 'newPassword';

        $user = User::factory()->create([
            'password' => bcrypt($oldPassword)
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('status', 200);

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * Reset the auth manager
     * 
     * @see https://stackoverflow.com/questions/57813795/method-illuminate-auth-requestguardlogout-does-not-exist-laravel-passport
     * 
     * @param array $guards (Optional) Array of auth guards you want to reset
     * 
     * @return void
     */
    protected function resetAuth(array $guards = null)
    {
        $guards = $guards ?: array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            $guard = $this->app['auth']->guard($guard);

            if ($guard instanceof \Illuminate\Auth\SessionGuard) {
                $guard->logout();
            }
        }

        $protectedProperty = new \ReflectionProperty($this->app['auth'], 'guards');
        $protectedProperty->setAccessible(true);
        $protectedProperty->setValue($this->app['auth'], []);
    }
}