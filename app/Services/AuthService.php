<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function register(array $data)
    {
        // check if user with email already exists
        $isUserExists = $this->userRepository->checkUser($data['email']);
        if ($isUserExists) {
            throw new \Exception('User with this email already exists');
        }

        $data['password'] = bcrypt($data['password']);

        $user = $this->userRepository->create($data);
        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function login(array $credentials): array
    {
        if (!auth()->attempt($credentials)) {
            throw new \Exception('Invalid username or password');
        }

        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
            throw new \Exception('Email not verified');
        }

        $expiresAt = now()->addMinutes(60);
        $token = $user->createToken(name: 'api-token', expiresAt: $expiresAt);

        return [
            'token' => $token->plainTextToken,
            'user' => $user
        ];
    }

    public function logout(): bool
    {
        $user = auth()->user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        $user->currentAccessToken()->delete();
        return true;
    }

    public function forgotPassword(string $email): bool
    {
        $status = Password::sendResetLink([
            'email' => $email
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new \Exception(__($status));
        }

        return true;
    }

    public function resetPassword(array $data): bool
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new \Exception(__($status));
        }

        return true;
    }
}
