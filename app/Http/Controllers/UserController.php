<?php

namespace App\Http\Controllers;

use App\Modules\User\Exceptions\UnknownStatusException;
use App\Modules\User\UserServiceInterface;
use App\Modules\Http\Message;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * User Module
     * @var UserServiceInterface $userServiceModule
     */
    protected UserServiceInterface $userServiceModule;

    /**
     * User Controller Constructor
     * 
     * @param UserServiceInterface $userServiceModule
     * 
     * @return void
     */
    public function __construct(UserServiceInterface $userServiceModule)
    {
        $this->userServiceModule = $userServiceModule;
    }

    /**
     * Retrieve current user's details
     * 
     * @param Request $request
     * @param Message $message
     * 
     * @return Response
     * 
     * @route GET api/v1/users/me
     */
    public function retrieve(Request $request, Message $message)
    {
        $user = $request->user();
        $userDetails = $this->userServiceModule->retrieve($user);

        $message->setContent(200, 'User data retrieved', '', [
            'users' => [
                'me' => $userDetails,
            ]
        ]);

        return $message->render();
    }

    /**
     * Create new user via signup
     * 
     * @param Request $request
     * @param Message $message
     * 
     * @return Response
     * 
     * @route POST api/v1/users/sign-up
     */
    public function store(Request $request, Message $message)
    {
        $firstName = $request->input('firstName');
        $lastName = $request->input('lastName');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $password = $request->input('password');
        
        $user = $this->userServiceModule->create($email, $firstName, $lastName, $phone, $password);

        if (!is_null($user)) {
            $message->setContent(201, 'User successfully created');
        } else {
            $message->setContent(400, 'User not created', 'User was not created. Please try again later.');
        }

        return $message->render();
    }

    /**
     * Update account information
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function update(Request $request, Message $message)
    {
        $user = $request->user();
        $firstName = $request->input('firstName');
        $lastName = $request->input('lastName');
        $phone = $request->input('phone');
        $email = $request->input('email');
        
        $isSuccess = $this->userServiceModule->update($user, $firstName, $lastName, $email, $phone);

        if ($isSuccess) {
            $message->setContent(200, 'User successfully updated');
        } else {
            $message->setContent(400, 'User not updated', 'User was not updated. Please try again later.');
        }

        return $message->render();
    }

    /**
     * Change the account's password
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function changePassword(Request $request, Message $message)
    {
        $user = $request->user();
        $oldPassword = $request->input('oldPassword');
        $newPassword = $request->input('newPassword');

        $isSuccess = $this->userServiceModule->changePassword($user, $oldPassword, $newPassword);
        
        if ($isSuccess) {
            $message->setContent(200, 'User password changed');
        } else {
            $message->setContent(400, 'User password not changed');
        }

        return $message->render();
    }

    /**
     * Delete user account
     * 
     * @todo Give warning to user when there is currently an item out for rent.
     *      Removing the account will lose the contact/record of the item.
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function delete(Request $request, Message $message)
    {
        $user = $request->user();
        $password = $request->input('password');

        $isSuccess = $this->userServiceModule->delete($user, $password);

        if ($isSuccess) {
            $message->setContent(200, 'User account deleted');
        } else {
            $message->setContent(400, 'User account not deleted');
        }

        return $message->render();
    }
}
