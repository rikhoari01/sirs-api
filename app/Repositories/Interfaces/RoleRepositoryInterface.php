<?php

namespace App\Repositories\Interfaces;

use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role;

    /**
     * @param int $id
     * @param array $data
     * @return Role
     */
    public function updateRole(int $id, array $data): Role;
}
