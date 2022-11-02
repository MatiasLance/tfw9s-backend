<?php

namespace App\Modules\Http\Tests;

use App\Modules\Http\Exceptions\NoErrorCodeException;
use App\Modules\Http\Problem;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProblemTest extends TestCase
{
    /**
     * Check problem response defaults
     * 
     * @return void
     */
    public function testDefaultProblemResponse()
    {
        $problem = new Problem();
        $errorCode = 'Err:test_error';
        $problem->setErrorCode($errorCode);
        $response = $problem->render();

        $this->assertTrue($response instanceof Response);
        $jsonContent = $response->getContent();
        $content = json_decode($jsonContent);

        // Check response status code is equal to status code indicated in response data
        $this->assertEquals($response->getStatusCode(), $content->status);
        
        // Check default status code is 400
        $this->assertEquals(400, $content->status);

        // Check default type is 'about:blank' according to RFC7807
        $this->assertEquals('about:blank', $content->type);

        // Check that unset properties does not show in response data
        $this->assertFalse(isset($content->title));
        $this->assertFalse(isset($content->detail));
        $this->assertFalse(isset($content->data));
    }

    /**
     * Populate Problem object and check its response
     * 
     * @return void
     */
    public function testPopulateProblemResponse()
    {
        $status = 413;
        $title = 'Test Error';
        $detail = 'This is a test error response';
        $errorCode = 'Err:test_error';
        $type = 'https://api.edi.com/docs/test';
        $data = [
            'email' => 'Email is required',
            'domain' => 'Domain is not a valid domain name',
        ];
        $name = 'Name field is required';

        $problem = new Problem();
        $problem->setStatus($status);
        $problem->setTitle($title);
        $problem->setDetail($detail);
        $problem->setData($data);
        $problem->setErrorCode($errorCode);
        $problem->setType($type);
        $problem->addDataAttribute('name', $name);

        $response = $problem->render();

        $jsonContent = $response->getContent();
        $content = json_decode($jsonContent);

        $this->assertEquals($status, $content->status);
        $this->assertEquals($title, $content->title);
        $this->assertEquals($detail, $content->detail);
        $this->assertEquals($errorCode, $content->errorCode);
        $this->assertEquals($type, $content->type);
        
        $this->assertEquals($data['email'], $content->data->email);
        $this->assertEquals($data['domain'], $content->data->domain);
        $this->assertEquals($name, $content->data->name);
    }

    /**
     * Problem must have an errorCode or else it throws NoErrorCodeException
     * 
     * @return void
     */
    public function testNoErrorCodeMustFail()
    {
        $problem = new Problem();
        $this->expectException(NoErrorCodeException::class);
        $problem->render();
    }
}