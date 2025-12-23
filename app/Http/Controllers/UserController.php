<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 10);
            $search = $request->query('search', '');

            $users = $this->userService->getAllUsers($page, $search);
            return $this->successResponse($users, 'Users retrieved successfully');
        } catch (\Throwable $th) {
            $this->errorResponse($th->getMessage());
        }
    }

    public function update(int $id, Request $request)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'phone_number' => 'sometimes|nullable|string|max:20',
                'address' => 'sometimes|nullable|string|max:500',
                'city' => 'sometimes|nullable|string|max:100',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'country' => 'sometimes|nullable|string|max:100',
            ]);

            $user = $this->userService->updateUser($id, $request->all());
            return $this->successResponse($user, 'User updated successfully');
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to update user', 500, $th->getMessage());
        }
    }
}
