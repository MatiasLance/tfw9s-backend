<?php

namespace App\Modules\User;

use App\Models\User;

interface UserServiceInterface
{
    /**
     * Retrieve a user's information
     * 
     * @param User $user
     * 
     * @return User
     */
    public function retrieve(User $user): User;

    /**
     * Create a new User instance
     * 
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $password
     * 
     * @return null|User
     */
    public function create(string $email, string $firstName, string $lastName, string $phone, string $password): ?User;

    /**
     * Update a user instance
     * 
     * @param User $initiator The user who initiated the update
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * 
     * @return bool
     */
    public function update(User $initiator, string $firstName, string $lastName, string $email, string $phone): bool;

    /**
     * Change user password
     * 
     * @param User $initiator The user who initiated the password change
     * @param string $oldPassword
     * @param string $newPassword
     * 
     * @return bool
     */
    public function changePassword(User $initiator, string $oldPassword, string $newPassword): bool;

    /**
     * Delete the user's account
     * 
     * @param User $user
     * @param string $password
     * 
     * @return bool
     */
    public function delete(User $user, string $password): bool;
}