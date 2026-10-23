<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(private readonly EmailVerificationService $verification) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): User
    {
        $user = DB::transaction(fn (): User => User::create($data));

        $this->verification->issue($user);

        return $user;
    }
}
