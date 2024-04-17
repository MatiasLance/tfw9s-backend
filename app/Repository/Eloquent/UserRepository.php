<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Modules\User\Classes\Status as UserStatus;
use App\Modules\User\Exceptions\EmailAlreadyUsedException;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\ItemRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @var ItemRepositoryInterface $itemRepository
     */
    protected ItemRepositoryInterface $itemRepository;

    /**
     * Indicate the model type. Used for polymorphic relationships.
     * 
     * @var string $userPolymorphicType
     */
    protected string $userPolymorphicType = 'App\Models\User';

    /**
     * User Repository Constructor
     * 
     * @param User $model
     * @param AddressRepositoryInterface $addressRepository
     * 
     * @return void
     */
    public function __construct(User $model, ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
        parent::__construct($model);
    }

    /**
     * Retrieve a user's information
     * 
     * @param User $user
     * 
     * @return User
     */
    public function retrieve(User $user): User
    {
        return $user;
    }

    /**
     * Create a new User
     * 
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $password
     * @param array $contactChannels
     * @param Address $address
     * @param UploadedFile $front Front photo of user's license
     * @param UploadedFile $back Back photo of user's license
     * 
     * @return User
     */
    public function create(string $email, string $firstName, string $lastName, string $phone, string $password): User
    {
        if (! $this->checkIfUnique('email', $email)) {
            throw new EmailAlreadyUsedException();
        }

        $user = new User();
        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->phone = $phone;
        $user->password = bcrypt($password);

        $user->save();

        return $user;
    }

    /**
     * Update an existing instance of User
     * 
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param array $contactChannels
     * @param Address $address
     * 
     * @return bool
     */
    public function update(
        int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
    ): bool
    {
        $user = $this->find($id);
        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->phone = $phone;

        $isSuccess = DB::transaction(function() use($user) {
            $isUserUpdated = $user->save();
            return $isUserUpdated;
        });

        return $isSuccess;
    }

    /**
     * Change user's password
     * 
     * @param int $id
     * @param string $newPassword
     * 
     * @return bool
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $user = $this->find($id);
        $user->password = bcrypt($newPassword);

        $isSuccess = DB::transaction(function() use($user) {
            return $user->save();
        });

        return $isSuccess;
    }
    
    /**
     * Delete a User instance
     * 
     * @todo More testing on what happens in the perspective of the item if a
     *      booking or review is removed.
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function delete(int $id): bool
    {
        $user = $this->find($id);
        
        $isSuccess = DB::transaction(function() use($user) {
            $user->items()->delete();
            return parent::delete($user->id);
        });

        return $isSuccess;
    }

    /**
     * Check if the value is a unique value. Returns true when the value is unique.
     * 
     * @param string $key Column name to check
     * @param mixed $value Value to check
     * 
     * @return bool
     */
    protected function checkIfUnique(string $key, $value): bool
    {
        return $this->model->where($key, $value)->doesntExist();
    }


}