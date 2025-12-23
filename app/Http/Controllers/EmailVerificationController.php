<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function verify(Request $request)
    {
        try {
            $data = $request->validate([
                'hash' => 'required|string',
                'email' => 'required|string',
            ]);

            $hash = $data['hash'];
            $email = Crypt::decryptString($data['email']);
            $user = $this->userService->findUserByEmail($email);

            if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return $this->errorResponse(
                    'Invalid verification link',
                    403
                );
            }

            if ($user->hasVerifiedEmail()) {
                return $this->errorResponse(
                    'Email already verified'
                );
            }

            $user->markEmailAsVerified();
            event(new Verified($user));

            return $this->successResponse(
                null,
                'Email verified successfully'
            );
        } catch (\Throwable $th) {
            \Log::error($th);
            return $this->errorResponse(
                'Email verification failed',
                500,
                $th->getMessage()
            );
        }
    }

    public function resend(Request $request)
    {
        $user = $this->userService->findUserByEmail($request->email);
        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse('null', 'Verification email resent');
    }
}
