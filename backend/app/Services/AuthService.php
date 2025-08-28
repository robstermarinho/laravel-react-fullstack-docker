<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;
use App\Contracts\AuthServiceInterface;

class AuthService implements AuthServiceInterface
{

    public function __construct()
    {
        Log::info('AuthService constructor');
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function register(array $data): array
    {
        $validator = $this->validateRegister($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Validate registration data
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateRegister(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
    }

    /**
     * Validate login data
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateLogin(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
    }

    /**
     * Authenticate user login
     *
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function login(array $credentials): array
    {
        $validator = $this->validateLogin($credentials);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!Auth::attempt($credentials)) {
            throw new \Exception('Invalid credentials');
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw new \Exception('User not found');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Logout user by revoking token
     *
     * @param string $token
     * @return bool
     * @throws \Exception
     */
    public function logout(string $token): bool
    {
        if (!$token) {
            throw new \Exception('Token not found');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            throw new \Exception('Invalid token');
        }

        $accessToken->delete();

        return true;
    }

    /**
     * Get authenticated user by token
     *
     * @param string $token
     * @return User
     * @throws \Exception
     */
    public function getUser(string $token): User
    {
        if (!$token) {
            throw new \Exception('Token not found');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            throw new \Exception('Invalid token');
        }

        return $accessToken->tokenable;
    }

    /**
     * Verify if token is valid
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        if (!$token) {
            return false;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken !== null;
    }
}
