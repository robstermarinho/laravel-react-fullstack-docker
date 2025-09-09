<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\AuthServiceInterface;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{


    public function __construct(
        protected AuthServiceInterface $authService,
        protected CacheService $cacheService
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->all());

            return response()->json($result, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request): JsonResponse
    {

        try {
            $result = $this->authService->login($request->all());

            return response()->json($result);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $this->extractBearerToken($request);
            $this->authService->logout($token);

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            $token = $this->extractBearerToken($request);
            $user = $this->authService->getUser($token);

            return response()->json([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Extract bearer token from request
     */
    private function extractBearerToken(Request $request): string
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new \Exception('Token not provided');
        }

        return $token;
    }

    /**
     * Test cache functionality with unique ID
     */
    public function testCache(Request $request): JsonResponse
    {
        try {
            $result = $this->cacheService->testCache();

            return response()->json([
                'message' => 'Cache test executed',
                'result' => $result,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cache test failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(Request $request): JsonResponse
    {
   

        try {
            $stats = $this->cacheService->getCacheStats();

            return response()->json([
                'message' => 'Cache statistics retrieved',
                'stats' => $stats,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get cache statistics',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Clear test cache
     */
    public function clearTestCache(Request $request): JsonResponse
    {
        try {
            $result = $this->cacheService->clearTestCache();

            return response()->json([
                'message' => $result ? 'Cache cleared successfully' : 'Cache was already empty',
                'success' => $result,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Return validation error response
     */
    private function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
