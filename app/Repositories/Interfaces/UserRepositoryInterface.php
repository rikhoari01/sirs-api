<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

/**
 * @extends BaseRepositoryInterface<User>
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User
     */
    public function findByEmail(string $email): User;
}

