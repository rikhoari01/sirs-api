<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

/**
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User
     */
    public function findByEmail(string $email): User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Check if a user with the given email exists.
     *
     * @param string $email
     * @return bool
     */
    public function checkUser(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
}
