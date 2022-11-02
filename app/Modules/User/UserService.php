<?php 

namespace App\Modules\User;

use App\Models\User as UserModel;
use App\Modules\Address\Classes\Address;
use App\Modules\User\Exceptions\IncorrectPasswordException;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieve a user's information
     * 
     * @param UserModel $user
     * 
     * @return User
     */
    public function retrieve(UserModel $user): UserModel
    {
        return $this->userRepository->retrieve($user);
    }

    /**
     * Create a new User instance
     * 
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $password
     * 
     * @return null|UserModel
     */
    public function create(string $email, string $firstName, string $lastName, string $phone, string $password): ?UserModel
    {
        return $this->userRepository->create($email, $firstName, $lastName, $phone, $password);
    }

    /**
     * Update a user instance
     * 
     * @param User $initiator The user who initiated the update
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $mobile
     * @param array $contactChannels
     * @param Address $address
     * 
     * @return bool
     */
    public function update(UserModel $initiator, string $firstName, string $lastName, string $email, string $phone): bool
    {
        return $this->userRepository->update($initiator->id, $firstName, $lastName, $email, $phone);
    }

    /**
     * Change user password
     * 
     * @param User $initiator The user who initiated the password change
     * @param string $oldPassword
     * @param string $newPassword
     * 
     * @return bool
     */
    public function changePassword(UserModel $initiator, string $oldPassword, string $newPassword): bool
    {
        if (Hash::check($oldPassword, $initiator->password)) {
            return $this->userRepository->changePassword($initiator->id, $newPassword);
        } else {
            throw new IncorrectPasswordException('Incorrect password given when changing password');
        }
    }

    /**
     * Delete the user's account
     * 
     * @param User $user
     * @param string $password
     * 
     * @return bool
     */
    public function delete(UserModel $user, string $password): bool
    {
        if (Hash::check($password, $user->password)) {
            return $this->userRepository->delete($user->id);
        } else {
            throw new IncorrectPasswordException('Password given does not match');
        }
    }
}