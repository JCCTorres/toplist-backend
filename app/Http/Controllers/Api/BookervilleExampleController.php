<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookervilleService;
use Illuminate\Http\JsonResponse;

class BookervilleExampleController extends Controller
{
    private BookervilleService $bookervilleService;

    public function __construct(BookervilleService $bookervilleService)
    {
        $this->bookervilleService = $bookervilleService;
    }

    /**
     * Exemplo de uso completo da API Bookerville
     */
    public function example(): JsonResponse
    {
        $examples = [
            'api_info' => [
                'description' => 'Bookerville API Integration',
                'version' => '1.0',
                'base_url' => config('bookerville.base_url'),
                'cache_duration' => config('bookerville.cache_duration') . ' seconds'
            ],
            
            'endpoints' => [
                'health_check' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/health',
                    'description' => 'Verificar status da API Bookerville'
                ],
                
                'clear_cache' => [
                    'method' => 'DELETE',
                    'url' => '/api/v1/bookerville/cache',
                    'description' => 'Limpar cache do Bookerville'
                ],
                
                'property_summary' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/properties/summary',
                    'description' => 'Lista resumo de todas as propriedades',
                    'parameters' => [
                        'sortField' => 'name|lastBooked|featuredSort|city|state (opcional)',
                        'sortOrder' => 'ASC|DESC (opcional)',
                        'stateFilter' => 'Filtro por estado (opcional)',
                        'cityFilter' => 'Filtro por cidade (opcional)',
                        'minSleeps' => 'Número mínimo de hóspedes (opcional)',
                        'maxSleeps' => 'Número máximo de hóspedes (opcional)',
                        'bedrooms' => 'Número de quartos (opcional)',
                        'bathrooms' => 'Número de banheiros (opcional)',
                        'useCache' => 'true|false (opcional, padrão: true)'
                    ],
                    'example_url' => '/api/v1/bookerville/properties/summary?sortField=name&sortOrder=ASC&minSleeps=2'
                ],
                
                'property_details' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/properties/{propertyId}/details',
                    'description' => 'Detalhes completos de uma propriedade específica',
                    'parameters' => [
                        'propertyId' => 'ID da propriedade (obrigatório na URL)',
                        'getDisplayText' => 'Y|N (opcional)',
                        'getFeatures' => 'Y|N (opcional)',
                        'getLocation' => 'Y|N (opcional)',
                        'getPhotos' => 'Y|N (opcional)',
                        'useCache' => 'true|false (opcional)'
                    ],
                    'example_url' => '/api/v1/bookerville/properties/PROP123/details?getPhotos=Y&getFeatures=Y'
                ],
                
                'property_availability' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/properties/{propertyId}/availability',
                    'description' => 'Disponibilidade de uma propriedade específica',
                    'parameters' => [
                        'propertyId' => 'ID da propriedade (obrigatório na URL)',
                        'startDate' => 'Data de início (YYYY-MM-DD, obrigatório)',
                        'endDate' => 'Data de fim (YYYY-MM-DD, obrigatório)',
                        'useCache' => 'true|false (opcional)'
                    ],
                    'example_url' => '/api/v1/bookerville/properties/PROP123/availability?startDate=2024-12-01&endDate=2024-12-15'
                ],
                
                'guest_reviews' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/properties/{propertyId}/reviews',
                    'description' => 'Avaliações de hóspedes para uma propriedade',
                    'parameters' => [
                        'propertyId' => 'ID da propriedade (obrigatório na URL)',
                        'useCache' => 'true|false (opcional)'
                    ],
                    'example_url' => '/api/v1/bookerville/properties/PROP123/reviews'
                ],
                
                'general_availability' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/availability',
                    'description' => 'Busca de disponibilidade geral',
                    'parameters' => [
                        'startDate' => 'Data de início (YYYY-MM-DD, obrigatório)',
                        'endDate' => 'Data de fim (YYYY-MM-DD, obrigatório)',
                        'numGuests' => 'Número de hóspedes (opcional)',
                        'propertyId' => 'ID da propriedade específica (opcional)',
                        'useCache' => 'true|false (opcional)'
                    ],
                    'example_url' => '/api/v1/bookerville/availability?startDate=2024-12-01&endDate=2024-12-15&numGuests=4'
                ],
                
                'multi_property_search' => [
                    'method' => 'GET',
                    'url' => '/api/v1/bookerville/search/multi-property',
                    'description' => 'Busca avançada em múltiplas propriedades',
                    'parameters' => [
                        'startDate' => 'Data de início (YYYY-MM-DD, obrigatório)',
                        'endDate' => 'Data de fim (YYYY-MM-DD, obrigatório)',
                        'numGuests' => 'Número de hóspedes (opcional)',
                        'stateFilter' => 'Filtro por estado (opcional)',
                        'cityFilter' => 'Filtro por cidade (opcional)',
                        'categoryFilter' => 'Filtro por categoria (opcional)',
                        'areaFilter' => 'Filtro por área (opcional)',
                        'minSleeps' => 'Número mínimo de hóspedes (opcional)',
                        'maxSleeps' => 'Número máximo de hóspedes (opcional)',
                        'bedrooms' => 'Número de quartos (opcional)',
                        'bathrooms' => 'Número de banheiros (opcional)',
                        'maxMiles' => 'Distância máxima em milhas (opcional)',
                        'zipcode' => 'Código postal (opcional)',
                        'latitude' => 'Latitude (opcional)',
                        'longitude' => 'Longitude (opcional)',
                        'useCache' => 'true|false (opcional)'
                    ],
                    'example_url' => '/api/v1/bookerville/search/multi-property?startDate=2024-12-01&endDate=2024-12-15&numGuests=4&stateFilter=FL&minSleeps=2'
                ]
            ],
            
            'configuration' => [
                'description' => 'Para configurar a integração com Bookerville, edite o arquivo .env:',
                'required_env_vars' => [
                    'BOOKERVILLE_BASE_URL' => 'URL base da API (padrão: https://www.bookerville.com)',
                    'BOOKERVILLE_API_KEY' => 'Sua chave de API do Bookerville (obrigatório)',
                    'BOOKERVILLE_ACCOUNT_ID' => 'ID da sua conta Bookerville (obrigatório)',
                    'BOOKERVILLE_CACHE_DURATION' => 'Duração do cache em segundos (padrão: 300)',
                    'BOOKERVILLE_TIMEOUT' => 'Timeout das requisições em segundos (padrão: 10)'
                ]
            ],
            
            'authentication' => [
                'description' => 'As rotas protegidas requerem autenticação Bearer Token',
                'protected_routes' => '/api/v1/bookerville/*',
                'public_routes' => '/api/v1/public/bookerville/*',
                'how_to_authenticate' => [
                    '1. POST /api/auth/login com email/password',
                    '2. Use o token retornado no header: Authorization: Bearer {token}'
                ]
            ],
            
            'response_format' => [
                'success_response' => [
                    'success' => true,
                    'data' => '... dados da API ...'
                ],
                'error_response' => [
                    'success' => false,
                    'message' => 'Descrição do erro',
                    'error' => 'CODIGO_ERRO'
                ]
            ]
        ];

        return response()->json($examples, 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Teste simples da conectividade
     */
    public function testConnection(): JsonResponse
    {
        try {
            // Teste apenas com parâmetros básicos sem cache
            $result = $this->bookervilleService->getPropertySummary(['useCache' => false]);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão com Bookerville API está funcionando',
                    'properties_found' => count($result['data']['properties'] ?? []),
                    'test_timestamp' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro na conexão com Bookerville API',
                    'error' => $result['error'] ?? 'UNKNOWN_ERROR',
                    'details' => $result['message'] ?? 'Erro desconhecido'
                ], 503);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no teste de conexão',
                'error' => 'CONNECTION_TEST_FAILED',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}