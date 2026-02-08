<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bookerville API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bookerville API integration
    | Default values are provided but can be overridden via environment variables
    |
    */

    'base_url' => env('BOOKERVILLE_BASE_URL', 'https://www.bookerville.com'),
    
    'api_key' => env('BOOKERVILLE_API_KEY', 'T7AL0LO0KN6QAYPI38OBI4SB2AF6P'),
    
    'account_id' => env('BOOKERVILLE_ACCOUNT_ID', '1538'),
    
    'cache_duration' => env('BOOKERVILLE_CACHE_DURATION', 300), // 5 minutos em segundos
    
    'timeout' => env('BOOKERVILLE_TIMEOUT', 10), // timeout em segundos
    
    'endpoints' => [
        'summary' => '/API-PropertySummary',
        'property_details' => '/API-PropertyDetails',
        'guest_reviews' => '/API-GuestReviews',
        'availability' => '/API-Availability',
        'property_availability' => '/API-PropertyAvailability',
        'multi_property_search' => '/API-Multi-Property-Availability-Search',
        'booking' => '/API-Booking',
        'payment' => '/API-Payment',
        'rates' => '/API-Rates',
    ],
    
    'defaults' => [
        'send_results_as' => 'xml',
        'photo_full_size' => 'Y',
        'sort_field' => 'lastBooked',
        'sort_order' => 'ASC',
        'currency' => 'USD',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'window_ms' => 15 * 60 * 1000, // 15 minutos
        'max_requests' => 100, // limite de 100 requests por IP
        'enabled' => env('BOOKERVILLE_RATE_LIMIT_ENABLED', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('BOOKERVILLE_CACHE_ENABLED', true),
        'duration' => env('BOOKERVILLE_CACHE_DURATION', 300), // 5 minutos
        'prefix' => 'bookerville_',
        'tags' => ['bookerville', 'api'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Security & Headers
    |--------------------------------------------------------------------------
    */
    'security' => [
        'verify_ssl' => env('BOOKERVILLE_VERIFY_SSL', true),
        'user_agent' => env('BOOKERVILLE_USER_AGENT', 'Laravel-Bookerville-Integration/1.0'),
        'max_retries' => env('BOOKERVILLE_MAX_RETRIES', 3),
        'retry_delay' => env('BOOKERVILLE_RETRY_DELAY', 1000), // milliseconds
    ],
];