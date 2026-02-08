<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientPropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPropertyController extends Controller
{
    protected ClientPropertyService $clientPropertyService;

    public function __construct(ClientPropertyService $clientPropertyService)
    {
        $this->clientPropertyService = $clientPropertyService;
    }

    /**
     * Listar todas as propriedades
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['owner', 'search', 'needs_sync']);
            
            $properties = $this->clientPropertyService->findPaginated($perPage, $filters);
            
            return response()->json([
                'success' => true,
                'data' => $properties->items(),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                    'last_page' => $properties->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar nova propriedade
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'airbnbId' => 'required|integer|unique:client_properties,airbnb_id',
                'airbnbUrl' => 'required|string',
                'title' => 'required|string',
                'houseNumber' => 'nullable|string',
                'owner' => 'nullable|string',
                'observations' => 'nullable|string',
                'address' => 'nullable|string',
            ]);

            $property = $this->clientPropertyService->create($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $property->toTypeScriptFormat(),
                'message' => 'Propriedade criada com sucesso'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar propriedade por Airbnb ID
     */
    public function show(int $airbnbId): JsonResponse
    {
        try {
            $property = $this->clientPropertyService->findByAirbnbId($airbnbId);
            
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $property->toTypeScriptFormat()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar propriedade
     */
    public function update(Request $request, int $airbnbId): JsonResponse
    {
        try {
            $request->validate([
                'airbnbUrl' => 'sometimes|string',
                'title' => 'sometimes|string',
                'houseNumber' => 'nullable|string',
                'owner' => 'nullable|string',
                'observations' => 'nullable|string',
                'address' => 'nullable|string',
            ]);

            $updated = $this->clientPropertyService->update($airbnbId, $request->all());
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }
            
            $property = $this->clientPropertyService->findByAirbnbId($airbnbId);
            
            return response()->json([
                'success' => true,
                'data' => $property->toTypeScriptFormat(),
                'message' => 'Propriedade atualizada com sucesso'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar propriedade
     */
    public function destroy(int $airbnbId): JsonResponse
    {
        try {
            $deleted = $this->clientPropertyService->delete($airbnbId);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Propriedade deletada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar propriedades que precisam de sincronização
     */
    public function needingSync(): JsonResponse
    {
        try {
            $properties = $this->clientPropertyService->findNeedingSync();
            
            return response()->json([
                'success' => true,
                'data' => $properties->map(fn($property) => $property->toTypeScriptFormat()),
                'count' => $properties->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedades para sincronização',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar dados do Bookerville
     */
    public function updateBookervilleData(Request $request, int $airbnbId): JsonResponse
    {
        try {
            $request->validate([
                'bookervilleData' => 'required|array'
            ]);

            $updated = $this->clientPropertyService->updateBookervilleData(
                $airbnbId, 
                $request->get('bookervilleData')
            );
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Dados do Bookerville atualizados com sucesso'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar dados do Bookerville',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar propriedades do JSON
     */
    public function importFromJson(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'properties' => 'required|array'
            ]);

            $result = $this->clientPropertyService->importFromJson($request->get('properties'));
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => "Importação concluída: {$result['created']} criadas, {$result['updated']} atualizadas, {$result['errors']} erros"
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar propriedades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas das propriedades
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->clientPropertyService->getStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar propriedade como sincronizada
     */
    public function markAsSynced(int $airbnbId): JsonResponse
    {
        try {
            $updated = $this->clientPropertyService->markAsSynced($airbnbId);
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Propriedade marcada como sincronizada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar propriedade como sincronizada',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
