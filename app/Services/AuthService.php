<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Realiza o login do usuário e retorna o token
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $tokenName = 'api-token-' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'message' => 'Login realizado com sucesso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => null // Sanctum tokens don't expire by default
        ];
    }

    /**
     * Realiza o logout do usuário (revoga token atual)
     *
     * @param User $user
     * @return array
     */
    public function logout(User $user): array
    {
        // Remove o token atual
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return [
            'message' => 'Logout realizado com sucesso'
        ];
    }

    /**
     * Revoga todos os tokens do usuário
     *
     * @param User $user
     * @return array
     */
    public function revokeAllTokens(User $user): array
    {
        $tokensCount = $user->tokens()->count();
        $user->tokens()->delete();

        return [
            'message' => 'Todos os tokens foram revogados',
            'revoked_tokens_count' => $tokensCount
        ];
    }

    /**
     * Retorna informações do usuário autenticado
     *
     * @param User $user
     * @return array
     */
    public function getUserInfo(User $user): array
    {
        $currentToken = $user->currentAccessToken();
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'tokens_count' => $user->tokens()->count(),
            'current_token' => $currentToken ? [
                'id' => $currentToken->id,
                'name' => $currentToken->name,
                'created_at' => $currentToken->created_at,
                'last_used_at' => $currentToken->last_used_at,
            ] : null
        ];
    }

    /**
     * Valida se o usuário existe e está ativo
     *
     * @param string $email
     * @return bool
     */
    public function userExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Retorna estatísticas de tokens do usuário
     *
     * @param User $user
     * @return array
     */
    public function getTokenStats(User $user): array
    {
        $tokens = $user->tokens();

        return [
            'total_tokens' => $tokens->count(),
            'active_tokens' => $tokens->whereNull('expires_at')->count(),
            'last_token_created' => $tokens->latest('created_at')->first()?->created_at,
            'last_token_used' => $tokens->whereNotNull('last_used_at')
                ->latest('last_used_at')->first()?->last_used_at,
        ];
    }
}