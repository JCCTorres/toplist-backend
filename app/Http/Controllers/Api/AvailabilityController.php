<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AvailabilityController extends Controller
{
    private AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Obtém a disponibilidade real de uma propriedade
     */
    public function getRealAvailability(Request $request, string $propertyId): JsonResponse
    {
        try {
            // Validar parâmetros da query
    
            $validator = Validator::make($request->all(), [
                // 'startDate' => 'sometimes|date|date_format:Y-m-d',
                // 'endDate' => 'sometimes|date|date_format:Y-m-d|after_or_equal:startDate',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Parâmetros inválidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 422
                ], 422);
            }

            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');

            $result = $this->availabilityService->getRealAvailability(
                $propertyId,
                $startDate,
                $endDate
            );

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $error) {
            Log::error('Erro no controller de disponibilidade: ' . $error->getMessage(), [
                'propertyId' => $propertyId,
                'startDate' => $request->query('startDate'),
                'endDate' => $request->query('endDate'),
                'trace' => $error->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erro interno do servidor',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Atualiza a configuração de disponibilidade de uma propriedade
     */
    public function updateAvailabilityConfig(Request $request, string $propertyId): JsonResponse
    {
        try {
            // Validar o corpo da requisição
            $validator = Validator::make($request->all(), [
                'config' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Configuração inválida',
                    'errors' => $validator->errors(),
                    'statusCode' => 422
                ], 422);
            }

            $config = $request->input('config', $request->all());

            $this->availabilityService->updateAvailabilityConfig($propertyId, $config);

            return response()->json([
                'success' => true,
                'message' => 'Configuração de disponibilidade atualizada com sucesso',
                'propertyId' => $propertyId,
                'statusCode' => 200
            ], 200);

        } catch (\Exception $error) {
            Log::error('Erro ao atualizar configuração de disponibilidade: ' . $error->getMessage(), [
                'propertyId' => $propertyId,
                'config' => $request->all(),
                'trace' => $error->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erro interno do servidor',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Obtém a configuração atual de disponibilidade
     */
    public function getAvailabilityConfig(Request $request): JsonResponse
    {
        try {
            $config = $this->availabilityService->getAvailabilityConfig();

            return response()->json([
                'success' => true,
                'data' => $config,
                'statusCode' => 200
            ], 200);

        } catch (\Exception $error) {
            Log::error('Erro ao obter configuração de disponibilidade: ' . $error->getMessage(), [
                'trace' => $error->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erro interno do servidor',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Health check para o serviço de disponibilidade
     */
    public function healthCheck(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'service' => 'AvailabilityService',
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => '1.0.0'
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'service' => 'AvailabilityService',
                'status' => 'unhealthy',
                'error' => $error->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Obtém estatísticas de disponibilidade para uma propriedade
     */
    public function getAvailabilityStats(Request $request, string $propertyId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'startDate' => 'sometimes|date|date_format:Y-m-d',
                'endDate' => 'sometimes|date|date_format:Y-m-d|after_or_equal:startDate',
                'groupBy' => 'sometimes|string|in:day,week,month',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Parâmetros inválidos',
                    'errors' => $validator->errors(),
                    'statusCode' => 422
                ], 422);
            }

            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');
            $groupBy = $request->query('groupBy', 'day');

            // Obter disponibilidade real
            $availability = $this->availabilityService->getRealAvailability($propertyId, $startDate, $endDate);

            if (!$availability['success']) {
                return response()->json($availability, 400);
            }

            $availableDates = $availability['data']['availableDates'];
            $totalDates = $availability['data']['totalAvailable'];
            $bookedStays = $availability['data']['bookedStays'];

            // Calcular estatísticas
            $stats = [
                'propertyId' => $propertyId,
                'period' => $availability['data']['period'],
                'summary' => [
                    'totalAvailableDates' => $totalDates,
                    'totalBookedStays' => $bookedStays,
                    'occupancyRate' => $bookedStays > 0 ? round(($bookedStays / ($totalDates + $bookedStays)) * 100, 2) : 0,
                    'averageNightlyRate' => $totalDates > 0 ? round(array_sum(array_column($availableDates, 'nightlyRate')) / $totalDates, 2) : 0
                ],
                'groupBy' => $groupBy,
                'generatedAt' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'statusCode' => 200
            ], 200);

        } catch (\Exception $error) {
            Log::error('Erro ao obter estatísticas de disponibilidade: ' . $error->getMessage(), [
                'propertyId' => $propertyId,
                'trace' => $error->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erro interno do servidor',
                'statusCode' => 500
            ], 500);
        }
    }
}