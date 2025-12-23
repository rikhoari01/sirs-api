<?php

namespace App\Services;

use App\Repositories\Eloquent\UserRepository;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function findUserById(int $id)
    {
        return $this->userRepository->find($id);
    }

    public function findUserByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function updateUser(int $userId, array $data)
    {
        // check user exists
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $this->userRepository->update($userId, $data);
        return $this->userRepository->find($userId);
    }

    public function getAllUsers($page = 15, $search = '')
    {
        $model = $this->userRepository->model;
        if (!empty($search)) {
            $model = $model->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $this->userRepository->model = $model;
        return $this->userRepository->paginate($page);
    }
}
