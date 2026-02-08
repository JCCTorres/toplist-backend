<?php

namespace App\Config;

/**
 * Configuração da API Bookerville
 * Similar ao arquivo de configuração do Node.js
 */
class BookervilleConfig
{
    /**
     * Configurações principais da API Bookerville
     */
    public static function getConfig(): array
    {
        return [
            'api_key' => env('BOOKERVILLE_API_KEY') ?: 'T7AL0LO0KN6QAYPI38OBI4SB2AF6P',
            'account_id' => env('BOOKERVILLE_ACCOUNT_ID') ?: '1538',
            'base_url' => env('BOOKERVILLE_BASE_URL') ?: 'https://www.bookerville.com',
            
            'endpoints' => [
                'summary' => '/API-PropertySummary',
                'property_details' => '/API-PropertyDetails',
                'availability' => '/API-Availability',
                'booking' => '/API-Booking',
                'payment' => '/API-Payment',
                'multi_property_search' => '/API-MultiPropertySearch',
                'rates' => '/API-Rates',
                'guest_reviews' => '/API-GuestReviews',
                'property_availability' => '/API-PropertyAvailability',
            ],
        ];
    }

    /**
     * Configurações gerais da API
     */
    public static function getApiConfig(): array
    {
        return [
            // Configurações de segurança
            'rate_limit' => [
                'window_ms' => 15 * 60 * 1000, // 15 minutos
                'max' => 100, // limite de 100 requests por IP
                'enabled' => env('BOOKERVILLE_RATE_LIMIT_ENABLED', true),
            ],
            
            // Configurações de cache
            'cache' => [
                'duration' => 5 * 60 * 1000, // 5 minutos em millisegundos
                'enabled' => env('BOOKERVILLE_CACHE_ENABLED', true),
                'prefix' => 'bookerville_',
                'tags' => ['bookerville', 'api'],
            ],
            
            // Configurações de requisição
            'request' => [
                'timeout' => env('BOOKERVILLE_TIMEOUT', 10), // segundos
                'verify_ssl' => env('BOOKERVILLE_VERIFY_SSL', true),
                'user_agent' => env('BOOKERVILLE_USER_AGENT', 'Laravel-Bookerville-Integration/1.0'),
                'max_retries' => env('BOOKERVILLE_MAX_RETRIES', 3),
                'retry_delay' => env('BOOKERVILLE_RETRY_DELAY', 1000), // millisegundos
            ],
            
            // Headers padrão
            'headers' => [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            
            // Parâmetros padrão para todas as requisições
            'default_params' => [
                'sendResultsAs' => 'xml',
                'photoFullSize' => 'Y',
                'sortField' => 'lastBooked',
                'sortOrder' => 'ASC',
                'currency' => 'USD',
            ],
        ];
    }

    /**
     * Obter URL completa para um endpoint
     */
    public static function getEndpointUrl(string $endpoint): string
    {
        $config = self::getConfig();
        $endpoints = $config['endpoints'];
        
        if (!isset($endpoints[$endpoint])) {
            throw new \InvalidArgumentException("Endpoint '{$endpoint}' não encontrado.");
        }
        
        return $config['base_url'] . $endpoints[$endpoint];
    }

    /**
     * Obter parâmetros de autenticação
     */
    public static function getAuthParams(): array
    {
        $config = self::getConfig();
        
        return [
            's3cr3tK3y' => $config['api_key'],
            'bkvAccountId' => $config['account_id'],
        ];
    }

    /**
     * Obter configurações de cache em segundos (para Laravel Cache)
     */
    public static function getCacheDurationInSeconds(): int
    {
        $apiConfig = self::getApiConfig();
        return intval($apiConfig['cache']['duration'] / 1000); // converter de ms para segundos
    }

    /**
     * Verificar se o cache está habilitado
     */
    public static function isCacheEnabled(): bool
    {
        $apiConfig = self::getApiConfig();
        return $apiConfig['cache']['enabled'];
    }

    /**
     * Verificar se o rate limiting está habilitado
     */
    public static function isRateLimitEnabled(): bool
    {
        $apiConfig = self::getApiConfig();
        return $apiConfig['rate_limit']['enabled'];
    }

    /**
     * Obter todas as configurações como array (para debug)
     */
    public static function getAllConfig(): array
    {
        return [
            'bookerville' => self::getConfig(),
            'api' => self::getApiConfig(),
        ];
    }

    /**
     * Validar se as configurações essenciais estão presentes
     */
    public static function validateConfig(): array
    {
        $config = self::getConfig();
        $errors = [];

        if (empty($config['api_key']) || $config['api_key'] === 'your_api_key_here') {
            $errors[] = 'BOOKERVILLE_API_KEY não configurado ou usando valor padrão';
        }

        if (empty($config['account_id']) || $config['account_id'] === 'your_account_id_here') {
            $errors[] = 'BOOKERVILLE_ACCOUNT_ID não configurado ou usando valor padrão';
        }

        if (empty($config['base_url'])) {
            $errors[] = 'BOOKERVILLE_BASE_URL não configurado';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'config' => $config,
        ];
    }
}