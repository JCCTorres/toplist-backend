<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\ClientPropertyController;
use App\Http\Controllers\Api\BookervilleController;
use App\Http\Controllers\Api\BookervilleExampleController;
use App\Http\Controllers\Api\BookervilleConfigController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\EmailController;

// Rota de teste
Route::get('/test', [TestController::class, 'test']);
Route::post('/test', [TestController::class, 'testPost']);

// Documentação e exemplos da API Bookerville
Route::get('/bookerville/docs', [BookervilleExampleController::class, 'example']);
Route::get('/bookerville/test-connection', [BookervilleExampleController::class, 'testConnection']);

// Configuração e diagnóstico do Bookerville
Route::prefix('bookerville/config')->group(function () {
    Route::get('/check', [BookervilleConfigController::class, 'checkConfig']);
    Route::get('/show', [BookervilleConfigController::class, 'getConfig']);
    Route::get('/test-connection', [BookervilleConfigController::class, 'testConnection']);
    Route::get('/endpoints', [BookervilleConfigController::class, 'testEndpoints']);
    Route::get('/cache-status', [BookervilleConfigController::class, 'cacheStatus']);
    Route::delete('/cache', [BookervilleConfigController::class, 'clearAllCache']);
});

// Rota pública para verificar status da API
Route::get('/status', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API está funcionando',
        'timestamp' => now()
    ]);
});

// Rotas de autenticação da API (públicas)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
});

// Rotas protegidas por autenticação Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de autenticação (protegidas)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/token-stats', [AuthController::class, 'tokenStats']);
    });

    // Informações do usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rotas protegidas da API
    Route::prefix('v1')->group(function () {
        // Client Properties Routes
        Route::prefix('client-properties')->group(function () {
            Route::get('/', [ClientPropertyController::class, 'index']);
            Route::post('/', [ClientPropertyController::class, 'store']);
            Route::get('/needing-sync', [ClientPropertyController::class, 'needingSync']);
            Route::get('/stats', [ClientPropertyController::class, 'stats']);
            Route::post('/import', [ClientPropertyController::class, 'importFromJson']);
            Route::get('/{airbnbId}', [ClientPropertyController::class, 'show']);
            Route::put('/{airbnbId}', [ClientPropertyController::class, 'update']);
            Route::delete('/{airbnbId}', [ClientPropertyController::class, 'destroy']);
            Route::patch('/{airbnbId}/bookerville-data', [ClientPropertyController::class, 'updateBookervilleData']);
            Route::patch('/{airbnbId}/mark-synced', [ClientPropertyController::class, 'markAsSynced']);
        });

        // Bookerville API Routes
        Route::prefix('/admin/bookerville')->group(function () {
            Route::get('/health', [BookervilleController::class, 'healthCheck']);
            Route::delete('/cache', [BookervilleController::class, 'clearCache']);
            Route::get('/home-cards', [BookervilleController::class, 'getHomeCards']);
            Route::get('/properties/cards', [BookervilleController::class, 'getAllPropertiesCards']);
            Route::post('/properties/{airbnbId}/airbnb-checkout', [BookervilleController::class, 'generateAirbnbCheckoutLink']);
            Route::get('/properties/summary', [BookervilleController::class, 'getPropertySummary']);
            Route::get('/properties/{propertyId}/details', [BookervilleController::class, 'getPropertyDetails']);
            Route::get('/properties/{propertyId}/availability', [BookervilleController::class, 'getPropertyAvailability']);
            Route::get('/properties/{propertyId}/reviews', [BookervilleController::class, 'getGuestReviews']);
            Route::get('/availability', [BookervilleController::class, 'getAvailability']);
            Route::post('/search', [BookervilleController::class, 'multiPropertySearch']);
        });

        // Properties Routes (Protegidas)
        Route::prefix('properties')->group(function () {
            Route::get('/health', [PropertyController::class, 'healthCheck']);
            Route::get('/stats', [PropertyController::class, 'getStats']);
            Route::get('/cards', [PropertyController::class, 'getAllPropertiesCards']);
            Route::get('/category/{category}', [PropertyController::class, 'getPropertiesByCategory']);
            Route::get('/category/{category}/with-images', [PropertyController::class, 'getPropertiesByCategoryWithImages']);
            Route::get('/{propertyId}', [PropertyController::class, 'getProperty']);
        });

        // Aqui você pode adicionar outras rotas específicas
    });
});

// Rotas públicas da API (sem autenticação)
Route::prefix('/bookerville')->group(function () {
    // Client Properties Routes (Públicas para teste)
    Route::prefix('client-properties')->group(function () {
        Route::get('/', [ClientPropertyController::class, 'index']);
        Route::post('/', [ClientPropertyController::class, 'store']);
        Route::get('/needing-sync', [ClientPropertyController::class, 'needingSync']);
        Route::get('/stats', [ClientPropertyController::class, 'stats']);
        Route::post('/import', [ClientPropertyController::class, 'importFromJson']);
        Route::get('/{airbnbId}', [ClientPropertyController::class, 'show']);
        Route::put('/{airbnbId}', [ClientPropertyController::class, 'update']);
        Route::delete('/{airbnbId}', [ClientPropertyController::class, 'destroy']);
        Route::patch('/{airbnbId}/bookerville-data', [ClientPropertyController::class, 'updateBookervilleData']);
        Route::patch('/{airbnbId}/mark-synced', [ClientPropertyController::class, 'markAsSynced']);
    });

    Route::get('/health', [BookervilleController::class, 'healthCheck']);
    Route::delete('/cache', [BookervilleController::class, 'clearCache']);
    Route::get('/home-cards', [BookervilleController::class, 'getHomeCards']);
    Route::get('/all-properties', [BookervilleController::class, 'getAllPropertiesCards']);
    Route::post('/properties/{airbnbId}/airbnb-checkout', [BookervilleController::class, 'generateAirbnbCheckoutLink']);
    Route::get('/properties/summary', [BookervilleController::class, 'getPropertySummary']);
    Route::get('/properties/{propertyId}/details', [BookervilleController::class, 'getPropertyDetails']);
    Route::get('/properties/{propertyId}/availability', [BookervilleController::class, 'getPropertyAvailability']);
    Route::get('/properties/{propertyId}/reviews', [BookervilleController::class, 'getGuestReviews']);
    Route::get('/availability', [BookervilleController::class, 'getAvailability']);

    Route::post('/airbnb/{airbnbId}/checkout-link', [BookervilleController::class, 'generateAirbnbCheckoutLink']);

    Route::get('/health', [AvailabilityController::class, 'healthCheck']);
    Route::get('/config', [AvailabilityController::class, 'getAvailabilityConfig']);
    Route::get('/properties/{propertyId}/real-availability', [AvailabilityController::class, 'getRealAvailability']);
    Route::get('/properties/{propertyId}/stats', [AvailabilityController::class, 'getAvailabilityStats']);
    Route::post('/properties/{propertyId}/config', [AvailabilityController::class, 'updateAvailabilityConfig']);



    // Properties Routes (Públicas para teste)
    Route::prefix('properties')->group(function () {
        Route::get('/health', [PropertyController::class, 'healthCheck']);
        Route::post('/search', [BookervilleController::class, 'multiPropertySearch']);
        Route::get('/stats', [PropertyController::class, 'getStats']);
        Route::get('/cards', [PropertyController::class, 'getAllPropertiesCards']);
        Route::get('/category/{category}', [PropertyController::class, 'getPropertiesByCategory']);
        Route::get('/category/{category}/with-images', [PropertyController::class, 'getPropertiesByCategoryWithImages']);
        Route::get('/{propertyId}', [PropertyController::class, 'getProperty']);
    });

    // Rotas públicas aqui
    // Email Routes (públicas para contact forms)

    // Exemplo: Route::get('toplist', [ToplistController::class, 'index']);
});

Route::prefix('email')->group(function () {
    Route::post('/contact', [EmailController::class, 'sendContactEmail']);
    Route::post('/management-request', [EmailController::class, 'sendManagementRequestEmail']);
    Route::get('/test-connection', [EmailController::class, 'testConnection']);
    Route::post('/tabinfo', [EmailController::class, 'enviarEmailTabinfo']); // Nova rota usando sua config PHP
});
