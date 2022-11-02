<?php

namespace App\Modules\Support\Tests;

use App\Modules\Http\Exceptions\NoErrorCodeException;
use App\Modules\Support\Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * Test using custom exception with minimal data given
     * 
     * @return void
     */
    public function test_custom_exception_minimal_correct()
    {
        $customException = new class extends Exception {
            protected string $errorCode = 'Err:test_exception';
        };

        $response = $customException->render(new Request());

        $responseContent = $response->content();
        $responseData = json_decode($responseContent, true);

        $this->assertTrue($response instanceof Response);
        $this->assertEquals($responseData['status'], 400);
        $this->assertEquals($responseData['errorCode'], 'Err:test_exception');
        $this->assertEquals($responseData['type'], 'about:blank');
    }

    /**
     * Test using custom exception with all data given
     * 
     * @return void
     */
    public function test_custom_exception_correct()
    {
        $customException = new class extends Exception {
            protected string $title = 'Test custom exception';
            protected string $errorCode = 'Err:test_exception';
            protected string $detail = 'This is a test to custom exception';

            public function getData(): ?array
            {
                $errors = [
                    'errors' => [
                        'email' => 'Email is not a valid email address.',
                        'password' => 'Password must be at least 8 characters.',
                    ],
                ];

                return $errors;
            }
        };

        $response = $customException->render(new Request());

        $responseContent = $response->content();
        $responseData = json_decode($responseContent, true);

        $this->assertTrue($response instanceof Response);
        $this->assertEquals($responseData['title'], 'Test custom exception');
        $this->assertEquals($responseData['status'], 400);
        $this->assertEquals($responseData['errorCode'], 'Err:test_exception');
        $this->assertEquals($responseData['detail'], 'This is a test to custom exception');
        $this->assertEquals($responseData['type'], 'about:blank');
        $this->assertArrayHasKey('errors', $responseData['data']);
        $this->assertArrayHasKey('email', $responseData['data']['errors']);
        $this->assertArrayHasKey('password', $responseData['data']['errors']);
        $this->assertEquals($responseData['data']['errors']['email'], 'Email is not a valid email address.');
        $this->assertEquals($responseData['data']['errors']['password'], 'Password must be at least 8 characters.');
    }

    public function test_custom_exception_no_error_code()
    {
        $customException = new class extends Exception {
            protected string $title = 'Test custom exception';
        };

        $this->expectException(NoErrorCodeException::class);
        $customException->render(new Request());
    }
}