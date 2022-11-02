<?php

namespace App\Modules\Http\Tests;

use App\Modules\Http\Message;
use Illuminate\Http\Response;
use Tests\TestCase;

class MessageTest extends TestCase
{
    /**
     * Render a message object and check its defaults
     * 
     * @return void
     */
    public function testDefaultMessageResponse()
    {
        $message = new Message();
        $response = $message->render();

        $this->assertTrue($response instanceof Response);
        $jsonContent = $response->getContent();
        $content = json_decode($jsonContent);

        // Check response status code is equal to status code indicated in response data
        $this->assertEquals($response->getStatusCode(), $content->status);
        
        // Check default status code is 200
        $this->assertEquals(200, $content->status);

        // Check that unset properties does not show in response data
        $this->assertFalse(isset($content->title));
        $this->assertFalse(isset($content->detail));
        $this->assertFalse(isset($content->data));
    }

    /**
     * Populate the Message object and check if the properties are correct after
     * rendered into response
     * 
     * @return void
     */
    public function testPopulateMessageResponse()
    {
        $status = 215;
        $title = 'Test Title';
        $detail = 'This is a test detail';
        $data = [
            'name' => 'Albert Einstein',
            'age' => 70,
        ];
        $sex = 'male';
        
        $message = new Message();
        $message->setStatus($status);
        $message->setTitle($title);
        $message->setDetail($detail);
        $message->setData($data);
        $message->addDataAttribute('sex', $sex);
        
        $response = $message->render();

        $jsonContent = $response->getContent();
        $content = json_decode($jsonContent);

        $this->assertEquals($status, $content->status);
        $this->assertEquals($title, $content->title);
        $this->assertEquals($detail, $content->detail);
        
        $this->assertEquals($data['name'], $content->data->name);
        $this->assertEquals($data['age'], $content->data->age);
        $this->assertEquals($sex, $content->data->sex);
    }
}