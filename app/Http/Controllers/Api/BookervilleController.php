<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProperty;
use App\Models\Property;
use App\Services\BookervilleService;
use App\Services\PriceCalculatorService;
use App\Services\PriceMarkupService;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookervilleController extends Controller
{
    /**
     * Bookerville property_id => Airbnb listing ID mapping
     * Fallback for when airbnb_id is not in the database
     */
    private const AIRBNB_ID_MAPPING = [
        '11684' => '857979998250100076',   // 1001 Baseball And Boardwalk Ct
        '10705' => '53159063',             // 3048 Cypress Gardens Ct
        '8558'  => '37538709',             // 1020 Baseball And Boardwalk Ct
        '11349' => '746746804361468563',   // 3050 Cypress Gardens Ct
        '11773' => '929745801403145100',   // 2130 Water Mania Ct
        '8372'  => '23063486',             // 3155 Wet N Wild Ct
        '9629'  => '42763626',             // 3111 Magic Kingdom Ct
        '9630'  => '43653417',             // 1120 Spaceport Ct
        '9627'  => '44125167',             // 3157 Wet N Wild Ct
        '11820' => '987377527010705358',   // 2116 Water Mania Ct
        '10706' => '54060170',             // 3178 Wet N Wild Ct
        '6073'  => '1028741013051744249',  // 3171 Wet N Wild Ct
        '11362' => '754743309149746143',   // 1114 Spaceport Ct
        '6582'  => '20483092',             // 2017 Disney MGM Studios Ct
        '10886' => '566352978010101798',   // 3194 Sea World Ct
        '10887' => '691695025778461811',   // 3145 Wet N Wild Ct
        '3929'  => '8608361',              // 3077 Rosie O Grady Ct
        '3594'  => '6629005',              // 3025 Universal Studios Ct
        '11685' => '858024866311666457',   // 3135 Magic Kingdom Ct
        '9631'  => '43834746',             // 1108 Spaceport Ct
        '3799'  => '7817121',              // 3200 Sea World Ct
        '8155'  => '32409750',             // 3148 Wet N Wild Ct
        '9628'  => '42661403',             // 3142 Wet N Wild Ct
        '10704' => '53158477',             // 3184 Sea World Ct
        '9929'  => '44125403',             // 3063 Cypress Gardens Ct
        '8499'  => '6629060',              // 3212 Sea World Ct
        '3032'  => '2206869',              // 3016 Bonfire Beach Dr
        '10369' => '48475974',             // 1556 Carey Palm Cir
        '9451'  => '40570414',             // 129 Madiera Beach Blvd
        '11350' => '765538654001747470',   // 157 Hideaway Beach Ln
        '2794'  => '4355718',              // 946 Park Terrace Circle
        '11686' => '859665505919638004',   // 172 Hideaway Beach Ln
        '11321' => '41366819',             // 4679 Golden Beach Ct
        '7624'  => '48476005',             // 115 Madiera Beach Blvd
    ];

    private BookervilleService $bookervilleService;
    private SyncService $syncService;

    public function __construct(
        BookervilleService $bookervilleService,
        SyncService $syncService
    ) {
        $this->bookervilleService = $bookervilleService;
        $this->syncService = $syncService;
    }

    /**
     * Get property summary
     */
    public function getPropertySummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sortField' => 'sometimes|string|in:name,lastBooked,featuredSort,city,state',
            'sortOrder' => 'sometimes|string|in:ASC,DESC',
            'getDisplayText' => 'sometimes|string|in:Y,N',
            'stateFilter' => 'sometimes|string|max:2',
            'cityFilter' => 'sometimes|string|max:100',
            'categoryFilter' => 'sometimes|string',
            'areaFilter' => 'sometimes|string',
            'minSleeps' => 'sometimes|integer|min:1',
            'maxSleeps' => 'sometimes|integer|min:1',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'maxMiles' => 'sometimes|integer|min:0',
            'zipcode' => 'sometimes|string|max:10',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'useCache' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            $result = $this->bookervilleService->getPropertySummary($params);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching property summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get property details by ID
     */
    public function getPropertyDetails(Request $request, string $propertyId): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['propertyId' => $propertyId]), [
            'propertyId' => 'required|string',
            'getDisplayText' => 'sometimes|string|in:Y,N',
            'getFeatures' => 'sometimes|string|in:Y,N',
            'getLocation' => 'sometimes|string|in:Y,N',
            'getPhotos' => 'sometimes|string|in:Y,N',
            'useCache' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            $result = $this->bookervilleService->getPropertyDetails($params);

            // Merge airbnb_id into response - check DB first, then fallback to mapping
            $localProp = Property::where('property_id', $propertyId)->first();
            $airbnbId = ($localProp && $localProp->airbnb_id)
                ? (string) $localProp->airbnb_id
                : (self::AIRBNB_ID_MAPPING[$propertyId] ?? null);

            if ($airbnbId) {
                if (is_array($result) && isset($result['data'])) {
                    $result['data']['airbnb_id'] = $airbnbId;
                } elseif (is_array($result)) {
                    $result['airbnb_id'] = $airbnbId;
                }
            }

            // Apply price markup to rates and fees
            if (is_array($result) && isset($result['data'])) {
                if (isset($result['data']['rates']) && is_array($result['data']['rates'])) {
                    $result['data']['rates'] = PriceMarkupService::applyToRates($result['data']['rates']);
                }
                if (isset($result['data']['fees']) && is_array($result['data']['fees'])) {
                    foreach (['cleaning_fee', 'additional_guest_fee'] as $feeKey) {
                        if (isset($result['data']['fees'][$feeKey]) && is_numeric($result['data']['fees'][$feeKey])) {
                            $result['data']['fees'][$feeKey] = PriceMarkupService::apply($result['data']['fees'][$feeKey]);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching property details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get property availability
     */
    public function getAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after:startDate',
            'numGuests' => 'sometimes|integer|min:1',
            'propertyId' => 'sometimes|string',
            'useCache' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            $result = $this->bookervilleService->getAvailability($params);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get property availability for specific property
     */
    public function getPropertyAvailability(Request $request, string $propertyId): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['propertyId' => $propertyId]), [
            'propertyId' => 'required|string',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after:startDate',
            'useCache' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            $result = $this->bookervilleService->getPropertyAvailability($params);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching property availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get guest reviews for property
     */
    public function getGuestReviews(Request $request, string $propertyId): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['propertyId' => $propertyId]), [
            'propertyId' => 'required|string',
            'useCache' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            $result = $this->bookervilleService->getGuestReviews($params);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching guest reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Multi-property availability search
     */
    public function multiPropertySearch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after:startDate',
            'numGuests' => 'sometimes|integer|min:1',
            'numAdults' => 'sometimes|integer|min:1',
            'numChildren' => 'sometimes|integer|min:0',
            'stateFilter' => 'sometimes|string|max:2',
            'cityFilter' => 'sometimes|string|max:100',
            'categoryFilter' => 'sometimes|string',
            'areaFilter' => 'sometimes|string',
            'minSleeps' => 'sometimes|integer|min:1',
            'maxSleeps' => 'sometimes|integer|min:1',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'maxMiles' => 'sometimes|integer|min:0',
            'zipcode' => 'sometimes|string|max:10',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'useCache' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();

            $result = $this->bookervilleService->multiPropertySearch($params);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing multi-property search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check for Bookerville API
     */
    public function healthCheck(): JsonResponse
    {
        try {
            // Test API connection with minimal request
            $result = $this->bookervilleService->getPropertySummary(['useCache' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Bookerville API is accessible',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bookerville API health check failed',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }

    /**
     * Get home cards with properties and resorts
     */
    public function getHomeCards(Request $request): JsonResponse
    {
        try {
            $limit = (int) ($request->query('limit', 6));
            $perCategory = max(3, (int) ceil($limit / 2));

            $cacheKey = "home_cards_response_{$limit}";
            $homeCards = Cache::get($cacheKey);

            if ($homeCards === null) {
                // Buscar propriedades e resorts com imagens válidas
                $properties = $this->syncService->getPropertiesByCategoryWithImages('property', $perCategory);
                $resorts = $this->syncService->getPropertiesByCategoryWithImages('resort', $perCategory);

                // Prefetch live rates for properties missing them in DB (avoids N+1 API calls in the loop)
                $liveRatesMap = [];
                $allProps = $properties->merge($resorts);
                $missingRateIds = $allProps->filter(function ($p) {
                    $details = $p->details ?? [];
                    $rates = $details['rates'] ?? [];
                    foreach ($rates as $rate) {
                        if (($rate['nightly_rate'] ?? 0) > 0 || ($rate['weekend_rate'] ?? 0) > 0) {
                            return false; // has rates in DB
                        }
                    }
                    return true; // needs live fetch
                })->pluck('property_id')->all();

                if (!empty($missingRateIds)) {
                    Log::info("[getHomeCards] Prefetching live rates for " . count($missingRateIds) . " properties");
                    foreach ($missingRateIds as $pid) {
                        try {
                            $liveDetails = $this->bookervilleService->getPropertyDetails([
                                'propertyId' => $pid
                            ]);
                            if ($liveDetails['success'] && !empty($liveDetails['data']['rates'])) {
                                $liveRatesMap[$pid] = $liveDetails['data']['rates'];
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to prefetch live rates for {$pid}: " . $e->getMessage());
                        }
                    }
                }

                // Formatar dados para o frontend
                $formatCard = function ($property) use ($liveRatesMap) {
                    $details = $property->details ?? [];

                    // Buscar título do cliente pelo endereço se existir
                    $clientTitle = null;
                    $address = $details['address'] ?? '';
                    if (!empty($address)) {
                        if (is_array($address)) {
                            $address = implode(', ', array_filter($address));
                        }
                        $clientTitle = $this->syncService->getClientTitleByAddress((string) $address);
                    }

                    // Determinar título final
                    $title = $clientTitle
                        ?: $property->title
                        ?: "{$details['bedrooms']} Bedrooms / {$details['bathrooms']} Baths / {$details['city']}";

                    // Read nightly rate from DB (persisted during sync)
                    $nightlyRate = null;
                    $rates = $details['rates'] ?? [];
                    foreach ($rates as $rate) {
                        if (($rate['nightly_rate'] ?? 0) > 0) {
                            $nightlyRate = (float) $rate['nightly_rate'];
                            break;
                        }
                    }
                    if ($nightlyRate === null) {
                        foreach ($rates as $rate) {
                            if (($rate['weekend_rate'] ?? 0) > 0) {
                                $nightlyRate = (float) $rate['weekend_rate'];
                                break;
                            }
                        }
                    }

                    // Fallback to prefetched live rates if DB had none
                    if ($nightlyRate === null && isset($liveRatesMap[$property->property_id])) {
                        $liveRates = $liveRatesMap[$property->property_id];
                        foreach ($liveRates as $rate) {
                            if (($rate['nightly_rate'] ?? 0) > 0) {
                                $nightlyRate = (float) $rate['nightly_rate'];
                                break;
                            }
                        }
                        if ($nightlyRate === null) {
                            foreach ($liveRates as $rate) {
                                if (($rate['weekend_rate'] ?? 0) > 0) {
                                    $nightlyRate = (float) $rate['weekend_rate'];
                                    break;
                                }
                            }
                        }
                    }

                    return [
                        'id' => $property->property_id,
                        'title' => $title,
                        'subtitle' => ($details['name'] ?? '') . (($details['propertyType'] ?? $details['property_type'] ?? '') ? ' • ' . ($details['propertyType'] ?? $details['property_type'] ?? '') : ''),
                        'guests' => ($details['maxGuests'] ?? $details['max_guests'] ?? 0) . " guests • {$details['bedrooms']} beds • {$details['bathrooms']} baths",
                        'image' => $details['mainImage'] ?? $details['main_image'] ?? $property->main_image,
                        'category' => $property->category,
                        'city' => $details['city'] ?? '',
                        'state' => $details['state'] ?? '',
                        'airbnb_id' => $property->airbnb_id ? (string) $property->airbnb_id : (self::AIRBNB_ID_MAPPING[$property->property_id] ?? null),
                        'nightly_rate' => $nightlyRate !== null ? PriceMarkupService::apply($nightlyRate) : null,
                    ];
                };

                $homeCards = [
                    'properties' => $properties->map($formatCard)->values(),
                    'resorts' => $resorts->map($formatCard)->values(),
                    'timestamp' => now()->toISOString()
                ];

                // Only cache if at least one card has a valid nightly_rate
                // to avoid poisoning the cache with "Contact for Pricing" data
                $allCards = collect($homeCards['properties'])->merge($homeCards['resorts']);
                $hasAnyRate = $allCards->contains(fn ($card) => $card['nightly_rate'] !== null);

                if ($hasAnyRate) {
                    Cache::put($cacheKey, $homeCards, 3600);
                } else {
                    Log::warning("[getHomeCards] Skipping cache — all nightly_rate values are null for limit={$limit}");
                }
            }

            return response()->json([
                'success' => true,
                'data' => $homeCards
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar os cards da home',
                'details' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Get all properties cards with optional filtering
     */
    public function getAllPropertiesCards(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|string|in:resort,property',
                'limit' => 'sometimes|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = $request->query('category');
            $limit = $request->query('limit') ? (int) $request->query('limit') : null;

            // Usar o método do SyncService que já formata os cards
            $result = $this->syncService->getAllPropertiesCards($category, $limit);

            return response()->json($result);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as propriedades',
                'details' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Airbnb checkout link
     */
    public function generateAirbnbCheckoutLink(Request $request, string $airbnbId)
    {
        try {
            $validator = Validator::make(array_merge($request->all(), ['airbnbId' => $airbnbId]), [
                'airbnbId' => 'required|string',
                'address' => 'sometimes|string',
                'checkin' => 'required|date_format:Y-m-d',
                'checkout' => 'required|date_format:Y-m-d|after:checkin',
                'numberOfGuests' => 'required|integer|min:1',
                'numberOfAdults' => 'required|integer|min:1',
                'numberOfChildren' => 'sometimes|integer|min:0',
                'numberOfInfants' => 'sometimes|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Parâmetros inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            $address = $data['address'] ?? null;
            $property = Property::query()->where('property_id', $airbnbId)->first();

            $searchParams = collect($data)->except(['airbnbId', 'address'])->toArray();

            // Validar ID do Airbnb (simples validação)
            if (!$this->isValidAirbnbId($airbnbId)) {
                return response()->json([
                    'success' => false,
                    'error' => 'INVALID_AIRBNB_ID',
                    'message' => 'ID do Airbnb inválido'
                ], 400);
            }

            $clientProperty = null;

            if ($address) {
               
                // Se o endereço foi fornecido, buscar diretamente por ele
                $clientProperty = $this->syncService->getClientPropertyByAddress($address);
            } else {
                // Fallback: buscar detalhes da propriedade no Bookerville e depois por endereço
                try {
                    $bookervilleResponse = $this->bookervilleService->getPropertyDetails(['propertyId' => $airbnbId]);

                    if (!$bookervilleResponse['success'] || !isset($bookervilleResponse['data'])) {
                        return response()->json([
                            'success' => false,
                            'error' => 'BOOKERVILLE_PROPERTY_NOT_FOUND',
                            'message' => "Propriedade {$airbnbId} não encontrada no Bookerville"
                        ], 404);
                    }

                    $bookervilleProperty = $bookervilleResponse['data'];

                    $propertyAddress = strtolower($bookervilleProperty['address']['address1'] ?? '');

                    if (!$propertyAddress) {
                        return response()->json([
                            'success' => false,
                            'error' => 'NO_ADDRESS_FOUND',
                            'message' => 'Endereço não encontrado para esta propriedade'
                        ], 404);
                    }

                    // Buscar propriedade correspondente na tabela client_properties
                    $clientProperty = $this->syncService->getClientPropertyByAddress($propertyAddress);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'error' => 'BOOKERVILLE_API_ERROR',
                        'message' => 'Erro ao buscar propriedade no Bookerville: ' . $e->getMessage()
                    ], 500);
                }
            }

            if (!$clientProperty) {
                return response()->json([
                    'success' => false,
                    'error' => 'CLIENT_PROPERTY_NOT_FOUND',
                    'message' => "Propriedade com endereço \"" . ($address ?: 'similar') . "\" não encontrada no sistema"
                ], 404);
            }

            // Construir URL de checkout para Airbnb usando o padrão /book/stays/{id}
            $originalUrl = $clientProperty->airbnb_url ?? '';
           
            if (!$originalUrl) {
                return response()->json([
                    'success' => false,
                    'error' => 'NO_AIRBNB_URL',
                    'message' => 'URL do Airbnb não encontrada para esta propriedade'
                ], 404);
            }

            // Gerar a URL final de checkout baseada no airbnbId (path) e parâmetros de busca
            $checkoutUrl = $this->buildAirbnbCheckoutUrl($clientProperty->airbnb_id, $searchParams);

            // Se for solicitado redirect direto (query param ?redirect=1), retornar redirect HTTP
            if ($request->boolean('redirect')) {
                return redirect()->away($checkoutUrl);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'checkoutUrl' => $checkoutUrl,
                    'propertyInfo' => [
                        'title' => $clientProperty->title,
                        'houseNumber' => $clientProperty->house_number,
                        'owner' => $clientProperty->owner,
                        'address' => $clientProperty->address,
                        'bookervilleId' => $airbnbId,
                        'airbnbId' => $clientProperty->airbnb_id,
                        'originalAirbnbUrl' => $originalUrl
                    ],
                    'searchInfo' => [
                        'searchMethod' => $address ? 'direct_address' : 'bookerville_address',
                        'searchedAddress' => $address ?: 'from_bookerville',
                        'matchedAddress' => $clientProperty->address
                    ]
                ]
            ]);
        } catch (\Exception $error) {
            $errorMessage = 'Erro interno do servidor';
            $statusCode = 500;

            if ($error->getMessage()) {
                $errorMessage = $error->getMessage();

                // Mapear erros específicos para códigos HTTP apropriados
                if (
                    str_contains($errorMessage, 'Data de checkout deve ser posterior') ||
                    str_contains($errorMessage, 'Formato de data inválido')
                ) {
                    $statusCode = 400;
                }
            }

            return response()->json([
                'success' => false,
                'error' => 'CHECKOUT_LINK_ERROR',
                'message' => $errorMessage
            ], $statusCode);
        }
    }

    /**
     * Validate Airbnb ID format
     */
    private function isValidAirbnbId(string $airbnbId): bool
    {
        // Simples validação - ID deve ser numérico e ter pelo menos 4 dígitos
        return is_numeric($airbnbId) && strlen($airbnbId) >= 4;
    }

    /**
     * Add checkout parameters to Airbnb URL
     */
    private function addCheckoutParamsToUrl(string $originalUrl, array $searchParams): string
    {
        $url = parse_url($originalUrl);

        if (!$url) {
            throw new \Exception('URL do Airbnb inválida');
        }

        // Extrair query parameters existentes
        $existingParams = [];
        if (isset($url['query'])) {
            parse_str($url['query'], $existingParams);
        }

        // Mantenho por compatibilidade, mas recomendamos usar buildAirbnbCheckoutUrl
        $airbnbParams = [
            'check_in' => $searchParams['checkin'],
            'check_out' => $searchParams['checkout'],
            'guests' => $searchParams['numberOfGuests'],
            'adults' => $searchParams['numberOfAdults']
        ];

        // Adicionar parâmetros opcionais se fornecidos
        if (isset($searchParams['numberOfChildren']) && $searchParams['numberOfChildren'] > 0) {
            $airbnbParams['children'] = $searchParams['numberOfChildren'];
        }

        if (isset($searchParams['numberOfInfants']) && $searchParams['numberOfInfants'] > 0) {
            $airbnbParams['infants'] = $searchParams['numberOfInfants'];
        }

        // Mesclar com parâmetros existentes (novos parâmetros têm prioridade)
        $allParams = array_merge($existingParams, $airbnbParams);

        // Reconstruir URL
        $scheme = $url['scheme'] ?? 'https';
        $host = $url['host'] ?? '';
        $path = $url['path'] ?? '';
        $fragment = isset($url['fragment']) ? '#' . $url['fragment'] : '';

        $newQuery = http_build_query($allParams);

        return $scheme . '://' . $host . $path . '?' . $newQuery . $fragment;
    }

    /**
     * Build Airbnb checkout URL using /book/stays/{id} and the parameter names expected
     */
    private function buildAirbnbCheckoutUrl(string $airbnbId, array $searchParams): string
    {
        // Map parameters to the format requested by the frontend
        $params = [
            'checkin' => $searchParams['checkin'] ?? null,
            'checkout' => $searchParams['checkout'] ?? null,
            'numberOfGuests' => $searchParams['numberOfGuests'] ?? 1,
            'numberOfAdults' => $searchParams['numberOfAdults'] ?? 1,
            'guestCurrency' => $searchParams['guestCurrency'] ?? 'BRL',
            'productId' => $airbnbId,
            'isWorkTrip' => isset($searchParams['isWorkTrip']) ? filter_var($searchParams['isWorkTrip'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' : 'false',
            'numberOfChildren' => $searchParams['numberOfChildren'] ?? 0,
            'numberOfInfants' => $searchParams['numberOfInfants'] ?? 0,
            'numberOfPets' => $searchParams['numberOfPets'] ?? 0,
        ];

        // Remove nulls
        $params = array_filter($params, function ($v) {
            return $v !== null;
        });

        $query = http_build_query($params);

        return 'https://www.airbnb.com.br/book/stays/' . rawurlencode($airbnbId) . '?' . $query;
    }

    /**
     * Get price estimate for a property stay
     */
    public function getPriceEstimate(Request $request, string $propertyId): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['propertyId' => $propertyId]), [
            'propertyId' => 'required|string',
            'checkIn' => 'required|date_format:Y-m-d',
            'checkOut' => 'required|date_format:Y-m-d|after:checkIn',
            'guests' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            $checkIn = $params['checkIn'];
            $checkOut = $params['checkOut'];
            $guests = $params['guests'] ?? 2;

            // Get property details from Bookerville
            $detailsResponse = $this->bookervilleService->getPropertyDetails(['propertyId' => $propertyId]);

            if (!$detailsResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch property details',
                    'error' => $detailsResponse['error'] ?? 'Unknown error'
                ], 400);
            }

            $propertyData = $detailsResponse['data'] ?? [];
            $rates = $propertyData['rates'] ?? [];
            $fees = $propertyData['fees'] ?? [];

            // Calculate price estimate
            $priceCalculator = new PriceCalculatorService();
            $estimate = $priceCalculator->calculateStayPrice(
                $rates,
                $fees,
                $checkIn,
                $checkOut,
                $guests,
                2 // freeGuests default
            );

            // Apply price markup to estimate
            if (!isset($estimate['error'])) {
                $estimate = PriceMarkupService::applyToBreakdown($estimate);
            }

            // Check for calculation errors
            if (isset($estimate['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $estimate['error'],
                    'data' => $estimate
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $estimate,
                'property_id' => $propertyId,
                'property_name' => $propertyData['name'] ?? ''
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating price estimate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear Bookerville cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->bookervilleService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Bookerville cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing Bookerville cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
