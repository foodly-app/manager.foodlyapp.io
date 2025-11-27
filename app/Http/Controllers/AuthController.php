<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            Log::info('Login attempt:', $credentials);

            $result = $this->authService->login($credentials);

            Log::info('Login result:', $result);

            // Store token in session for subsequent requests
            if (isset($result['token'])) {
                session(['partner_token' => $result['token']]);
                session(['partner_user' => $result['user']]);
                session(['partner_context' => $result['context']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $result['token'] ?? null,
                'user' => $result['user'] ?? null,
                'context' => $result['context'] ?? null,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Login validation failed:', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Login failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Test login endpoint with proper response format
     */
    public function testLogin(): JsonResponse
    {
        try {
            $credentials = [
                'email' => 'manager@foodly.pro',
                'password' => 'Manager1234',
            ];

            Log::info('Test login attempt:', $credentials);

            $result = $this->authService->login($credentials);

            Log::info('Test login result:', $result);

            // Store token in session for subsequent requests
            if (isset($result['token'])) {
                session(['partner_token' => $result['token']]);
                session(['partner_user' => $result['user']]);
                session(['partner_context' => $result['context']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $result['token'] ?? null,
                    'user' => $result['user'] ?? null,
                    'context' => $result['context'] ?? null,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Test login failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get bootstrap data (organizations and restaurants)
     */
    public function bootstrap(): JsonResponse
    {
        try {
            $result = $this->authService->bootstrap();

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            Log::error('Bootstrap failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Switch context (organization or restaurant)
     */
    public function switchContext(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_id' => ['nullable', 'integer'],
                'restaurant_id' => ['nullable', 'integer'],
            ]);

            $result = $this->authService->switchContext(
                $validated['organization_id'] ?? null,
                $validated['restaurant_id'] ?? null
            );

            // Update session context
            if (isset($result['context'])) {
                session(['partner_context' => $result['context']]);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            Log::error('Context switch failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get authenticated user info
     */
    public function me(): JsonResponse
    {
        try {
            $result = $this->authService->me();

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Me failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get user profile
     */
    public function getProfile(): JsonResponse
    {
        try {
            $result = $this->authService->getProfile();

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Get profile failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'surname' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'country_code' => ['sometimes', 'string', 'max:10'],
                'locale' => ['sometimes', 'string', 'in:ka,en'],
            ]);

            $result = $this->authService->updateProfile($data);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Update profile failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'max:2048'],
            ]);

            $result = $this->authService->uploadAvatar($request->file('avatar'));

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Upload avatar failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(): JsonResponse
    {
        try {
            $result = $this->authService->deleteAvatar();

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Delete avatar failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $result = $this->authService->changePassword($data);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Change password failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get initial dashboard data
     */
    public function initialDashboard(): JsonResponse
    {
        try {
            $result = $this->authService->initialDashboard();

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Initial dashboard failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            // Only call API logout if we have a session token
            if (session('partner_token')) {
                $this->authService->setToken(session('partner_token'));
                $this->authService->logout();
            }

            // Clear session data
            session()->forget(['partner_token', 'partner_user', 'partner_context']);
            session()->flush();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } catch (Exception $e) {
            Log::error('Logout failed:', ['message' => $e->getMessage()]);

            // Even if API logout fails, clear local session
            session()->forget(['partner_token', 'partner_user', 'partner_context']);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
