<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct()
    {
        $this->roleService = new RoleService();
    }

    public function index(Request $request)
    {
        $page = $request->input('per_page', 1);
        $search = $request->query('search');

        $roles = $this->roleService->getAllRoles($page, $search);
        return $this->successResponse($roles, 'Roles retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|unique:roles,name',
                'permissions' => 'array'
            ]);

            $role = $this->roleService->createRole($validated);

            return $this->successResponse($role, 'Role created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $role = $this->roleService->getRoleById($id);
            return $this->successResponse($role, 'Role retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|unique:roles,name,' . $id,
                'permissions' => 'array'
            ]);

            $role = $this->roleService->updateRole($id, $validated);

            return $this->successResponse($role, 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->roleService->deleteRole($id);
            return $this->successResponse(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function assignRole(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $roleNames = $request->input('role_names');

            $this->roleService->assignRole($userId, $roleNames);

            return $this->successResponse(null, 'Role assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
