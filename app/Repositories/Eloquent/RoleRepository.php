<?php

namespace App\Repositories\Eloquent;

use http\Client\Curl\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Role();
    }

    public function createRole(array $data): Role
    {
        $role = $this->model->create([
            'name' => $data['name'],
            'guard_name' => 'api'
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function updateRole(int $id, array $data): Role
    {
        $role = $this->model->findOrFail($id);
        $role->update([
            'name' => $data['name']
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function deleteRole(int $id): bool
    {
        return DB::table('roles')->where('id', $id)->delete();
    }
}
