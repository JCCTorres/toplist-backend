<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login para API - gera token Sanctum
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao realizar login',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Logout para API - revoga token atual
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->logout($request->user());
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao realizar logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoga todos os tokens do usuário
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function revokeAll(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->revokeAllTokens($request->user());
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao revogar tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna informações do usuário autenticado
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->getUserInfo($request->user());
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter informações do usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna estatísticas de tokens do usuário
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tokenStats(Request $request): JsonResponse
    {
        try {
            $stats = $this->authService->getTokenStats($request->user());
            
            return response()->json([
                'message' => 'Estatísticas de tokens',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica se um email existe no sistema
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = $this->authService->userExists($request->email);
        
        return response()->json([
            'exists' => $exists
        ], 200);
    }
}