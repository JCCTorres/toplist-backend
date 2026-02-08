<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    private SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Busca todas as propriedades formatadas como cards
     */
    public function getAllPropertiesCards(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string|in:resort,property',
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = $request->query('category');
            $limit = $request->query('limit') ? (int) $request->query('limit') : null;

            $result = $this->syncService->getAllPropertiesCards($category, $limit);
            
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as propriedades',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Busca propriedades por categoria
     */
    public function getPropertiesByCategory(Request $request, string $category): JsonResponse
    {
        $validator = Validator::make(
            array_merge($request->all(), ['category' => $category]), 
            [
                'category' => 'required|string|in:resort,property',
                'limit' => 'sometimes|integer|min:1|max:100'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $limit = $request->query('limit') ? (int) $request->query('limit') : 10;
            $properties = $this->syncService->getPropertiesByCategory($category, $limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'properties' => $properties,
                    'count' => $properties->count(),
                    'category' => $category,
                    'limit' => $limit
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => "Não foi possível carregar propriedades da categoria {$category}",
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Busca propriedades por categoria com imagens válidas
     */
    public function getPropertiesByCategoryWithImages(Request $request, string $category): JsonResponse
    {
        $validator = Validator::make(
            array_merge($request->all(), ['category' => $category]), 
            [
                'category' => 'required|string|in:resort,property',
                'limit' => 'sometimes|integer|min:1|max:100'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $limit = $request->query('limit') ? (int) $request->query('limit') : 10;
            $properties = $this->syncService->getPropertiesByCategoryWithImages($category, $limit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'properties' => $properties,
                    'count' => $properties->count(),
                    'category' => $category,
                    'limit' => $limit
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => "Não foi possível carregar propriedades da categoria {$category} com imagens",
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Busca uma propriedade específica
     */
    public function getProperty(string $propertyId): JsonResponse
    {
        try {
            $property = \App\Models\Property::where('property_id', $propertyId)->first();
            
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $property
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => "Não foi possível carregar a propriedade {$propertyId}",
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtém estatísticas das propriedades
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->syncService->getSyncStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as estatísticas',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Health check do serviço de propriedades
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $stats = $this->syncService->getSyncStats();
            
            return response()->json([
                'success' => true,
                'message' => 'Property service is healthy',
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property service health check failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }
}