<?php

namespace App\Http\Controllers;

use App\Modules\Mail\MailServiceInterface;
use Illuminate\Http\Request;

class ContactController extends Controller
{

    /**
     *  Mail Service
     * 
     * @var MailServiceInterface $mailService
     */
    protected MailServiceInterface $mailService;

    public function __construct(MailServiceInterface $mailService)
    {
        $this->mailService = $mailService;
    }

    public function sendMessage(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');
        
        $data = [
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ];
        $this->mailService->sendContactForm($data);
    }
}
