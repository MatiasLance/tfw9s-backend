<?php

namespace App\Repository;

use App\Models\User;

/**
 * UserRepositoryInterface
 */
interface UserRepositoryInterface
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
     * Create a new User
     * 
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $password
     * 
     * @return User
     */
    public function create(
        string $email,
        string $firstName,
        string $lastName,
        string $phone,
        string $password,
    ): User;

    /**
     * Update an existing instance of User
     * 
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * 
     * @return bool
     */
    public function update(
        int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
    ): bool;

    /**
     * Change user's password
     * 
     * @param int $id
     * @param string $newPassword
     * 
     * @return bool
     */
    public function changePassword(int $id, string $newPassword): bool;


    /**
     * Delete a User instance
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function delete(int $id): bool;
}