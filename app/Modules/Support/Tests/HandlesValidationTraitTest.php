<?php

namespace App\Modules\Support\Tests;

use App\Modules\Support\Traits\HandlesValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HandlesValidationTraitTest extends TestCase
{

    /**
     * Anonymous Class used to contain the handlesValidation trait
     * 
     * @var $handlesValidation
     */
    protected $handlesValidation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlesValidation = new class {
            use HandlesValidation;
        };
    }

    /**
     * Test handling validator function when given ValidatorException
     * 
     * @return void
     */
    public function test_handle_validator_error_validator_exception()
    {
        $data = [
            'username' => 'user',
            'email' => 'user.com',
            'password' => 'password',
        ];

        $validator = Validator::make($data, [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $exception = new ValidationException($validator);
        $response = $this->handlesValidation->handleValidationError($exception);
        $responseContent = $response->content();
        $responseData = json_decode($responseContent, true);

        $this->assertEquals($responseData['status'], 400);
        $this->assertEquals($responseData['title'], "The given data was invalid");
        $this->assertEquals($responseData['errorCode'], "Err:user_input_invalid");
        $this->assertArrayHasKey('errors', $responseData['data']);

        $this->assertArrayHasKey('email', $responseData['data']['errors']);
        $this->assertEquals($responseData['data']['errors']['email'][0], 'The email must be a valid email address.');
    }

    /**
     * Test handling validator function when given ValidatorException
     * 
     * @return void
     */
    public function test_handle_validator_error_message_bag()
    {
        $data = [
            'username' => 'user',
            'email' => 'user.com',
            'password' => 'password',
        ];

        $validator = Validator::make($data, [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $response = $this->handlesValidation->handleValidationError($validator->errors());
        $responseContent = $response->content();
        $responseData = json_decode($responseContent, true);

        $this->assertEquals($responseData['status'], 400);
        $this->assertEquals($responseData['title'], "The given data was invalid");
        $this->assertEquals($responseData['errorCode'], "Err:user_input_invalid");
        $this->assertArrayHasKey('errors', $responseData['data']);

        $this->assertArrayHasKey('email', $responseData['data']['errors']);
        $this->assertEquals($responseData['data']['errors']['email'][0], 'The email must be a valid email address.');
    }
}