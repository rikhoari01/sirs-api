<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed'
            ]);

            $user = $this->authService->register($data);

            return $this->successResponse(
                $user,
                'Registration successful, please verify email',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage()
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $result = $this->authService->login($data);

            return $this->successResponse(
                $result,
                'Login successful'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401
            );
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();
            return $this->successResponse(
                $user,
                'User details fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401
            );
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();

            return $this->successResponse(
                null,
                'Logged out'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401
            );
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $this->authService->forgotPassword($request->email);

        return $this->successResponse(null, 'Password reset link sent');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $this->authService->resetPassword($request->all());

        return $this->successResponse(null, 'Password reset successful, please login');
    }
}
