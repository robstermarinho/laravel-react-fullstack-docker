<?php

namespace App\Contracts;

use App\Models\User;

/**
 * Interface AuthServiceInterface
 * 
 * Define o contrato para serviços de autenticação
 */
interface AuthServiceInterface
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(array $data): array;

    /**
     * Validate registration data
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateRegister(array $data): \Illuminate\Contracts\Validation\Validator;

    /**
     * Validate login data
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateLogin(array $data): \Illuminate\Contracts\Validation\Validator;

    /**
     * Authenticate user login
     *
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function login(array $credentials): array;

    /**
     * Logout user by revoking token
     *
     * @param string $token
     * @return bool
     * @throws \Exception
     */
    public function logout(string $token): bool;

    /**
     * Get authenticated user by token
     *
     * @param string $token
     * @return User
     * @throws \Exception
     */
    public function getUser(string $token): User;

    /**
     * Verify if token is valid
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool;
}
