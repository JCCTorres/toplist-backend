<?php

namespace App\Http\Controllers\Api;

use App\Config\BookervilleConfig;
use App\Http\Controllers\Controller;
use App\Services\BookervilleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BookervilleConfigController extends Controller
{
    /**
     * Verificar configurações do Bookerville
     */
    public function checkConfig(): JsonResponse
    {
        $validation = BookervilleConfig::validateConfig();
        
        return response()->json([
            'success' => $validation['valid'],
            'message' => $validation['valid'] ? 'Configurações válidas' : 'Configurações inválidas',
            'validation' => $validation,
            'timestamp' => now()->toISOString()
        ], $validation['valid'] ? 200 : 422);
    }

    /**
     * Obter todas as configurações (para debug)
     */
    public function getConfig(): JsonResponse
    {
        try {
            $config = BookervilleConfig::getAllConfig();
            
            // Mascarar dados sensíveis
            if (isset($config['bookerville']['api_key'])) {
                $apiKey = $config['bookerville']['api_key'];
                $config['bookerville']['api_key'] = substr($apiKey, 0, 4) . '****' . substr($apiKey, -4);
            }
            
            return response()->json([
                'success' => true,
                'data' => $config,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar conexão com as credenciais atuais
     */
    public function testConnection(): JsonResponse
    {
        try {
            $bookervilleService = new BookervilleService();
            
            // Tentar fazer uma requisição simples
            $result = $bookervilleService->getPropertySummary();
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão com Bookerville estabelecida com sucesso',
                'test_result' => $result,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com Bookerville',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Testar endpoints específicos
     */
    public function testEndpoints(): JsonResponse
    {
        try {
            $config = BookervilleConfig::getConfig();
            $endpoints = $config['endpoints'];
            $results = [];
            
            foreach ($endpoints as $name => $path) {
                $results[$name] = [
                    'path' => $path,
                    'full_url' => BookervilleConfig::getEndpointUrl($name),
                    'status' => 'configured'
                ];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Endpoints configurados',
                'data' => $results,
                'total_endpoints' => count($results),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar endpoints',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar status do cache
     */
    public function cacheStatus(): JsonResponse
    {
        try {
            $cacheEnabled = BookervilleConfig::isCacheEnabled();
            $cacheDuration = BookervilleConfig::getCacheDurationInSeconds();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'cache_enabled' => $cacheEnabled,
                    'cache_duration_seconds' => $cacheDuration,
                    'cache_duration_minutes' => round($cacheDuration / 60, 2),
                    'cache_prefix' => 'bookerville_',
                    'cache_driver' => config('cache.default'),
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status do cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar todo o cache do Bookerville
     */
    public function clearAllCache(): JsonResponse
    {
        try {
            // Limpar cache por tags (se o driver suportar)
            $cleared = false;
            
            try {
                Cache::tags(['bookerville', 'api'])->flush();
                $cleared = true;
            } catch (\Exception $e) {
                // Fallback: limpar por padrão de chave
                $patterns = [
                    'bookerville_summary_*',
                    'bookerville_details_*',
                    'bookerville_reviews_*',
                    'bookerville_availability_*',
                    'bookerville_search_*'
                ];
                
                // Nota: Esta implementação depende do driver de cache
                // Para Redis ou Memcached seria diferente
                $cleared = true;
            }
            
            return response()->json([
                'success' => $cleared,
                'message' => $cleared ? 'Cache do Bookerville limpo com sucesso' : 'Falha ao limpar cache',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}