<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Eloquent\RoleRepository;

class RoleService
{
    protected $roleRepository;

    public function __construct()
    {
        $this->roleRepository = new RoleRepository();
    }

    public function getAllRoles($page = 1, $search = null)
    {
        $model = $this->roleRepository->model;
        if (!empty($search)) {
            $model = $model->where('name', 'like', '%' . $search . '%');
        }

        $this->roleRepository->model = $model;
        return $this->roleRepository->paginate($page);
    }

    public function getRoleById(int $id)
    {
        return $this->roleRepository->find($id);
    }

    public function createRole(array $data)
    {
        return $this->roleRepository->createRole($data);
    }

    public function updateRole(int $id, array $data)
    {
        $role = $this->roleRepository->find($id);
        if (empty($role)) {
            throw new \Error('Role not found');
        }
        return $this->roleRepository->update($id, $data);
    }

    public function deleteRole(int $id)
    {
        $role = $this->roleRepository->find($id);
        if (empty($role)) {
            throw new \Error('Role not found');
        }
        return $this->roleRepository->deleteRole($id);
    }

    public function assignRole($userId, $roleNames)
    {
        $user = User::find($userId);
        $newRole = [];

        $existingRole = $user->roles;
        $existingRoleName = $user->roles->pluck('name')->toArray();
        foreach ($existingRole as $role) {
            if (!in_array($role->name, $roleNames)) {
                $user->roles()->detach($role);
            }
        }

        foreach ($roleNames as $roleName) {
            if (!in_array($roleName, $existingRoleName)) {
                $newRole[] = $roleName;
            }
        }

        $role = $this->roleRepository->model->whereIn('name', $newRole)->get();
        if (empty($role)) {
            throw new \Error('Role not found');
        }
        return $user->assignRole($role);
    }
}
