<?php

namespace App\Services;

use App\Config\BookervilleConfig;
use App\Models\BookervilleProperty;
use App\Models\ClientProperty;
use App\Models\Property;
use App\Services\PriceMarkupService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class BookervilleService
{
    private string $baseUrl;
    private string $apiKey;
    private string $accountId;
    private int $cacheDuration;

    public function __construct()
    {
        $config = BookervilleConfig::getConfig();

        $this->baseUrl = $config['base_url'];
        $this->apiKey = $config['api_key'];
        $this->accountId = $config['account_id'];
        $this->cacheDuration = BookervilleConfig::getCacheDurationInSeconds();
    }

    /**
     * Fazer requisição para a API         }
    }

    /**
     * Parser para disponibilidade geral
     */
    private function parseAvailabilityXml(string $xmlData): array
    {
        $xml = new SimpleXMLElement($xmlData);
        $properties = [];

        if (isset($xml->Properties->Property)) {
            foreach ($xml->Properties->Property as $property) {
                $properties[] = [
                    'property_id' => (string) $property['id'] ?? '',
                    'name' => (string) $property->Name ?? '',
                    'available' => (string) $property->Available ?? 'N',
                    'min_stay' => (int) $property->MinStay ?? 0,
                    'rate' => (float) $property->Rate ?? 0,
                    'currency' => (string) $property->Currency ?? 'USD',
                    'check_in' => (string) $property->CheckIn ?? '',
                    'check_out' => (string) $property->CheckOut ?? '',
                    'booking_url' => (string) $property->BookingURL ?? ''
                ];
            }
        }

        return [
            'properties' => $properties,
            'search_criteria' => [
                'start_date' => (string) $xml->SearchCriteria->StartDate ?? '',
                'end_date' => (string) $xml->SearchCriteria->EndDate ?? '',
                'guests' => (int) $xml->SearchCriteria->Guests ?? 0
            ]
        ];
    }

    /**
     * Fazer requisição para a API do Bookerville
     */
    private function makeRequest(string $endpoint, array $params = []): array
    {

        $authParams = [
            's3cr3tK3y' => $this->apiKey,
            'bkvAccountId' => $this->accountId,
            ...$params
        ];

        $endpointMap = [
            'summary' => '/API-PropertySummary',
            'property-details' => '/API-PropertyDetails',
            'guest-reviews' => '/API-GuestReviews',
            'availability' => '/API-Availability',
            'property-availability' => '/API-PropertyAvailability',
            'multi-property-search' => '/API-Multi-Property-Availability-Search'
        ];

        $apiEndpoint = $endpointMap[$endpoint] ?? $endpoint;
        $url = $this->baseUrl . $apiEndpoint;

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/xml',
                    'User-Agent' => 'Laravel-Bookerville-Client/1.0'
                ])
                ->get($url, $authParams);

            if (!$response->successful()) {
                throw new \Exception("Erro na API do Bookerville: {$response->status()} - {$response->body()}");
            }

            return [
                'success' => true,
                'data' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error("Erro na requisição Bookerville: " . $e->getMessage(), [
                'endpoint' => $endpoint,
                'params' => $params
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    /**
     * Obter resumo de propriedades
     */
    public function getPropertySummary(array $params = []): array
    {
        $cacheKey = "bookerville_summary_{$this->accountId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            if (!$this->validateCredentials()) {
                return [
                    'success' => false,
                    'error' => 'INVALID_CREDENTIALS',
                    'message' => 'Credenciais da API inválidas'
                ];
            }

            $response = $this->makeRequest('summary');

            if (!$response['success']) {

                return $response;
            }



            try {
                $summaryData = $this->parseSummaryXml($response['data']);

                return [
                    'success' => true,
                    'data' => $summaryData
                ];
            } catch (\Exception $e) {
                Log::error("Erro ao processar summary XML: " . $e->getMessage());

                return [
                    'success' => false,
                    'error' => 'XML_PARSE_ERROR',
                    'message' => 'Erro ao processar resposta da API'
                ];
            }
        });
    }

    /**
     * Obter detalhes de uma propriedade
     */
    public function getPropertyDetails(array $params): array
    {
        $propertyId = $params['propertyId'] ?? null;

        if (!$propertyId) {
            return [
                'success' => false,
                'error' => 'MISSING_PROPERTY_ID',
                'message' => 'Property ID é obrigatório'
            ];
        }

        $cacheKey = "bookerville_details_{$propertyId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($propertyId, $params) {
            if (!$this->validateCredentials()) {
                return [
                    'success' => false,
                    'error' => 'INVALID_CREDENTIALS',
                    'message' => 'Credenciais da API inválidas'
                ];
            }

            $response = $this->makeRequest('property-details', ['bkvPropertyId' => $propertyId]);

            if (!$response['success']) {
                return $response;
            }

            try {
                $propertyDetails = $this->parsePropertyDetailsXml($response['data'], $propertyId);

                return [
                    'success' => true,
                    'data' => $propertyDetails
                ];
            } catch (\Exception $e) {
                Log::error("Erro ao processar property details XML: " . $e->getMessage());

                return [
                    'success' => false,
                    'error' => 'XML_PARSE_ERROR',
                    'message' => 'Erro ao processar detalhes da propriedade'
                ];
            }
        });
    }

    /**
     * Obter avaliações de hóspedes
     */
    public function getGuestReviews(array $params): array
    {
        $propertyId = $params['propertyId'] ?? null;

        if (!$propertyId) {
            return [
                'success' => false,
                'error' => 'MISSING_PROPERTY_ID',
                'message' => 'Property ID é obrigatório'
            ];
        }

        $cacheKey = "bookerville_reviews_{$propertyId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($propertyId) {
            if (!$this->validateCredentials()) {
                return [
                    'success' => false,
                    'error' => 'INVALID_CREDENTIALS',
                    'message' => 'Credenciais da API inválidas'
                ];
            }

            $response = $this->makeRequest('guest-reviews', ['bkvPropertyId' => $propertyId]);

            if (!$response['success']) {
                return $response;
            }

            try {
                $reviews = $this->parseGuestReviewsXml($response['data'], $propertyId);

                return [
                    'success' => true,
                    'data' => $reviews
                ];
            } catch (\Exception $e) {
                Log::error("Erro ao processar guest reviews XML: " . $e->getMessage());

                return [
                    'success' => false,
                    'error' => 'XML_PARSE_ERROR',
                    'message' => 'Erro ao processar avaliações'
                ];
            }
        });
    }

    /**
     * Obter disponibilidade da propriedade
     */
    public function getPropertyAvailability(array $params): array
    {
        $propertyId = $params['propertyId'] ?? null;

        if (!$propertyId) {
            return [
                'success' => false,
                'error' => 'MISSING_PROPERTY_ID',
                'message' => 'Property ID é obrigatório'
            ];
        }

        $cacheKey = "bookerville_availability_{$propertyId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($propertyId, $params) {
            if (!$this->validateCredentials()) {
                return [
                    'success' => false,
                    'error' => 'INVALID_CREDENTIALS',
                    'message' => 'Credenciais da API inválidas'
                ];
            }

            $response = $this->makeRequest('property-availability', ['bkvPropertyId' => $propertyId]);

            if (!$response['success']) {
                return $response;
            }

            try {
                $availability = $this->parsePropertyAvailabilityXml($response['data'], $propertyId);

                return [
                    'success' => true,
                    'data' => $availability
                ];
            } catch (\Exception $e) {
                Log::error("Erro ao processar availability XML: " . $e->getMessage());

                return [
                    'success' => false,
                    'error' => 'XML_PARSE_ERROR',
                    'message' => 'Erro ao processar disponibilidade'
                ];
            }
        });
    }

    /**
     * Busca multi-propriedades
     */
    public function getMultiPropertySearch(array $searchParams): array
    {

        if (!$this->validateCredentials()) {
            return [
                'success' => false,
                'error' => 'INVALID_CREDENTIALS',
                'message' => 'Credenciais da API inválidas'
            ];
        }

        try {

            $requestXml = $this->buildMultiPropertySearchXml($searchParams);

            $url = $this->baseUrl . '/API-Multi-Property-Availability-Search?s3cr3tK3y=' . $this->apiKey;

            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/xml',
                    'Content-Type' => 'application/xml',
                    'User-Agent' => 'Laravel-Bookerville-Client/1.0'
                ])
                ->withBody($requestXml)
                ->post($url);

            if (!$response->successful()) {
                throw new \Exception("Erro na API: {$response->status()} - {$response->body()}");
            }

            $searchResults = $this->parseMultiPropertySearchXml($response->body(), $searchParams);

            return [
                'success' => true,
                'data' => $searchResults
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao executar multi-property search: ' . $e->getMessage(), ['searchParams' => $searchParams]);

            return [
                'success' => false,
                'error' => 'SEARCH_ERROR',
                'message' => 'Erro na busca de propriedades'
            ];
        }
    }

    /**
     * Busca geral de disponibilidade
     */
    public function getAvailability(array $params): array
    {
        $requiredParams = ['startDate', 'endDate'];

        foreach ($requiredParams as $param) {
            if (!isset($params[$param])) {
                return [
                    'success' => false,
                    'error' => 'MISSING_REQUIRED_PARAM',
                    'message' => "Parâmetro obrigatório: {$param}"
                ];
            }
        }

        $cacheKey = "bookerville_availability_" . md5(json_encode($params));

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($params) {
            if (!$this->validateCredentials()) {
                return [
                    'success' => false,
                    'error' => 'INVALID_CREDENTIALS',
                    'message' => 'Credenciais da API inválidas'
                ];
            }

            $apiParams = [
                'startDate' => $params['startDate'],
                'endDate' => $params['endDate']
            ];

            if (isset($params['numGuests'])) {
                $apiParams['numGuests'] = $params['numGuests'];
            }

            if (isset($params['propertyId'])) {
                $apiParams['bkvPropertyId'] = $params['propertyId'];
            }

            $response = $this->makeRequest('availability', $apiParams);

            if (!$response['success']) {
                return $response;
            }

            try {
                $availability = $this->parseAvailabilityXml($response['data']);

                return [
                    'success' => true,
                    'data' => $availability
                ];
            } catch (\Exception $e) {
                Log::error("Erro ao processar availability XML: " . $e->getMessage());

                return [
                    'success' => false,
                    'error' => 'XML_PARSE_ERROR',
                    'message' => 'Erro ao processar disponibilidade'
                ];
            }
        });
    }

    /**
     * Alias para getMultiPropertySearch para compatibilidade com controller
     */
    public function multiPropertySearch(array $params): array
    {

        return $this->getMultiPropertySearch($params);
    }

    /**
     * Sincronizar propriedade com dados do Bookerville
     */
    public function syncProperty(string $propertyId): array
    {
        try {
            // Buscar detalhes da propriedade no Bookerville
            $detailsResponse = $this->getPropertyDetails(['propertyId' => $propertyId]);

            if (!$detailsResponse['success']) {
                return $detailsResponse;
            }

            $bookervilleData = $detailsResponse['data'];

            // Buscar na nossa base de dados
            $clientProperty = ClientProperty::where('airbnb_id', $propertyId)->first();

            if ($clientProperty) {
                // Atualizar dados do Bookerville
                $clientProperty->update([
                    'bookerville_data' => $bookervilleData,
                    'last_sync' => now()
                ]);

                return [
                    'success' => true,
                    'message' => 'Propriedade sincronizada com sucesso',
                    'data' => $clientProperty->toTypeScriptFormat()
                ];
            }

            return [
                'success' => false,
                'error' => 'PROPERTY_NOT_FOUND',
                'message' => 'Propriedade não encontrada na base de dados'
            ];
        } catch (\Exception $e) {
            Log::error("Erro ao sincronizar propriedade: " . $e->getMessage());

            return [
                'success' => false,
                'error' => 'SYNC_ERROR',
                'message' => 'Erro ao sincronizar propriedade'
            ];
        }
    }

    /**
     * Sincronizar todas as propriedades
     */
    public function syncAllProperties(): array
    {
        try {
            $summaryResponse = $this->getPropertySummary();

            if (!$summaryResponse['success']) {
                return $summaryResponse;
            }

            $properties = $summaryResponse['data']['properties'] ?? [];
            $results = [
                'synced' => 0,
                'errors' => 0,
                'details' => []
            ];

            foreach ($properties as $property) {
                $propertyId = $property['id'] ?? '';

                if ($propertyId) {
                    $syncResult = $this->syncProperty($propertyId);

                    if ($syncResult['success']) {
                        $results['synced']++;
                    } else {
                        $results['errors']++;
                    }

                    $results['details'][] = [
                        'property_id' => $propertyId,
                        'success' => $syncResult['success'],
                        'message' => $syncResult['message'] ?? null
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $results
            ];
        } catch (\Exception $e) {
            Log::error("Erro ao sincronizar todas as propriedades: " . $e->getMessage());

            return [
                'success' => false,
                'error' => 'SYNC_ALL_ERROR',
                'message' => 'Erro ao sincronizar propriedades'
            ];
        }
    }

    /**
     * Parser para XML de resumo
     */
    private function parseSummaryXml(string $xmlData): array
    {
        $xml = new SimpleXMLElement($xmlData);
        $properties = [];

        if (isset($xml->Property)) {
            foreach ($xml->Property as $property) {
                $properties[] = [
                    'id' => (string) $property['property_id'],
                    'account_id' => (string) $property['bkvAccountId'],
                    'last_update' => (string) $property['last_update'],
                    'details_url' => (string) $property['property_details_api_url'],
                    'name' => (string) $property->PropertyName ?? '',
                    'manager_first_name' => (string) $property->managerFirstName ?? '',
                    'manager_last_name' => (string) $property->managerLastName ?? '',
                    'manager_phone' => (string) $property->managerPhone ?? '',
                    'business_name' => (string) $property->businessName ?? '',
                    'email_account' => (string) $property->emailAddressAccount ?? '',
                    'offline' => (string) $property->offLine === 'Y'
                ];
            }
        }

        return [
            'properties' => $properties,
            'total_count' => count($properties),
            'account_id' => $this->accountId,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Parser para XML de detalhes da propriedade
     */
    private function parsePropertyDetailsXml(string $xmlData, string $propertyId): array
    {
        $xml = new SimpleXMLElement($xmlData);

        // Processar dados básicos
        $property = [
            'id' => $propertyId,
            'name' => (string) $xml->PropertyName ?? '',
            'address' => [
                'address1' => (string) $xml->Address->Address1 ?? '',
                'city' => (string) $xml->Address->City ?? '',
                'state' => (string) $xml->Address->State ?? '',
                'zip_code' => (string) $xml->Address->ZipCode ?? '',
                'country' => (string) $xml->Address->Country ?? ''
            ],
            'details' => [
                'property_type' => (string) $xml->Details->PropertyType ?? '',
                'bedrooms' => (int) $xml->Details->Bedrooms['count'] ?? 0,
                'bathrooms' => (float) $xml->Details->Bathrooms['count'] ?? 0,
                'max_occupancy' => (int) $xml->Details->MaximumOccupancy ?? 0,
                'check_in' => (string) $xml->Details->CheckIn ?? '',
                'check_out' => (string) $xml->Details->CheckOut ?? ''
            ],
            'description' => (string) $xml->Description->PropertyDescription ?? '',
            'photos' => $this->parsePhotos($xml->Photos ?? null),
            'amenities' => $this->parseAmenities($xml->Amenities ?? null),
            'rates' => $this->parseRates($xml->Rates ?? null),
            'min_stays' => $this->parseMinStays($xml->MinStays ?? null),
            'fees' => $this->parseFees($xml->Fees ?? null),
            'book_ahead_days' => (int) $xml->BookAheadDays ?? 0,
            'last_update' => (string) $xml['last_update'] ?? now()->toISOString()
        ];

        return $property;
    }

    /**
     * Parser para fotos
     */
    private function parsePhotos($photosXml): array
    {
        $photos = [];

        if ($photosXml && isset($photosXml->Photo)) {
            foreach ($photosXml->Photo as $photo) {
                $url = (string) $photo->URL;
                if ($url) {
                    $photos[] = $url;
                }
            }
        }

        return $photos;
    }

    /**
     * Parser para amenidades
     */
    private function parseAmenities($amenitiesXml): array
    {
        $amenities = [];
        $amenitiesList = [];

        if ($amenitiesXml) {
            foreach ($amenitiesXml->children() as $name => $amenity) {
                $value = (string) $amenity['value'] ?? (string) $amenity;
                $amenities[$name] = $value;

                if (in_array($value, ['yes', 'Y', 'true', '1'])) {
                    $amenitiesList[] = ucfirst(str_replace('_', ' ', $name));
                }
            }
        }

        return [
            'raw' => $amenities,
            'list' => $amenitiesList
        ];
    }

    /**
     * Parser para tarifas
     */
    private function parseRates($ratesXml): array
    {
        $rates = [];

        if ($ratesXml && isset($ratesXml->Rate)) {
            foreach ($ratesXml->Rate as $rate) {
                $rates[] = [
                    'season' => (string) $rate->Label ?? '',
                    'start_date' => (string) $rate->StartDate ?? '',
                    'end_date' => (string) $rate->EndDate ?? '',
                    'nightly_rate' => (float) $rate->DailyWeeknightRate ?? 0,
                    'weekend_rate' => (float) $rate->DailyWeekendRate ?? 0,
                    'weekly_rate' => (float) $rate->WeeklyRate ?? 0,
                    'monthly_rate' => (float) $rate->MonthlyRate ?? 0,
                    'minimum_stay' => (int) $rate->MinimumStay ?? 1,
                    'weekend_nights' => (string) $rate->WeekendNights ?? ''
                ];
            }
        }

        return $rates;
    }

    /**
     * Parser para estada mínima
     */
    private function parseMinStays($minStaysXml): array
    {
        $minStays = [];

        if ($minStaysXml && isset($minStaysXml->MinStay)) {
            foreach ($minStaysXml->MinStay as $minStay) {
                $minStays[] = [
                    'season' => (string) $minStay->Season ?? '',
                    'start_date' => (string) $minStay->StartDate ?? '',
                    'end_date' => (string) $minStay->EndDate ?? '',
                    'minimum_nights' => (int) $minStay->MinimumNightsStay ?? 1,
                    'description' => (string) $minStay->Description ?? ''
                ];
            }
        }

        return $minStays;
    }

    /**
     * Parser para taxas
     */
    private function parseFees($feesXml): array
    {
        $fees = [];

        if ($feesXml) {
            $fees = [
                'cleaning_fee' => (float) $feesXml->CleaningFee ?? 0,
                'service_fee' => (float) $feesXml->ServiceFee ?? 0,
                'resort_fee' => (float) $feesXml->ResortFee ?? 0,
                'tax_rate' => (float) $feesXml->TaxRate ?? 0,
                'security_deposit' => (float) $feesXml->SecurityDeposit ?? 0,
                'pet_fee' => (float) $feesXml->PetFee ?? 0,
                'additional_guest_fee' => (float) $feesXml->AdditionalGuestFee ?? 0,
                'currency' => (string) $feesXml->Currency ?? 'USD',
                'notes' => (string) $feesXml->Notes ?? ''
            ];
        }

        return $fees;
    }

    /**
     * Parser para avaliações de hóspedes
     */
    private function parseGuestReviewsXml(string $xmlData, string $propertyId): array
    {
        $xml = new SimpleXMLElement($xmlData);
        $reviews = [];

        if (isset($xml->guestReview)) {
            foreach ($xml->guestReview as $review) {
                $reviews[] = [
                    'guest_name' => trim(((string) $review->guestFirstName) . ' ' . ((string) $review->guestLastName)) ?: 'Anônimo',
                    'rating' => (float) $review->score ?? 0,
                    'review_date' => (string) $review->datePosted ?? '',
                    'review_text' => (string) $review->guestComments ?? '',
                    'response' => (string) $review->managerResponse ?? null,
                    'review_id' => $propertyId . '-' . ((string) $review->datePosted) . '-' . ((string) $review->guestFirstName),
                    'title' => (string) $review->title ?? '',
                    'booking_start_date' => (string) $review->bookingStartDate ?? '',
                    'booking_end_date' => (string) $review->bookingEndDate ?? '',
                    'property_name' => (string) $review->propName ?? ''
                ];
            }
        }

        $totalRating = array_sum(array_column($reviews, 'rating'));
        $averageRating = count($reviews) > 0 ? round($totalRating / count($reviews), 1) : 0;

        return [
            'property_id' => $propertyId,
            'reviews' => $reviews,
            'average_rating' => $averageRating,
            'total_reviews' => count($reviews),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Parser para disponibilidade da propriedade
     */
    private function parsePropertyAvailabilityXml(string $xmlData, string $propertyId): array
    {
        $xml = new SimpleXMLElement($xmlData);
        $bookedStays = [];

        if (isset($xml->Availability->BookedStays->BookedStay)) {
            foreach ($xml->Availability->BookedStays->BookedStay as $stay) {
                $bookedStays[] = [
                    'arrival_date' => (string) $stay->ArrivalDate ?? '',
                    'departure_date' => (string) $stay->DepartureDate ?? '',
                    'booking_url' => (string) $stay->bkvBookingURL ?? ''
                ];
            }
        }

        return [
            'property_id' => $propertyId,
            'last_update' => (string) $xml['last_update'] ?? now()->toISOString(),
            'booked_stays' => $bookedStays,
            'total_booked_stays' => count($bookedStays),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Construir XML para busca multi-propriedades
     */
    private function buildMultiPropertySearchXml(array $searchParams): string
    {
        $startDate = $searchParams['startDate'] ?? '';
        $endDate = $searchParams['endDate'] ?? '';
        $numAdults = $searchParams['numAdults'] ?? 1;
        $numChildren = $searchParams['numChildren'] ?? 0;
        $sendResultsAs = $searchParams['sendResultsAs'] ?? 'xml';
        $photoFullSize = $searchParams['photoFullSize'] ?? 'Y';
        $sortField = $searchParams['sortField'] ?? 'lastBooked';
        $sortOrder = $searchParams['sortOrder'] ?? 'ASC';

        $numGuests = $numAdults + $numChildren;


        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <request>
        <bkvAccountId>{$this->accountId}</bkvAccountId>
        <startDate>{$startDate}</startDate>
        <endDate>{$endDate}</endDate>
        <numAdults>{$numAdults}</numAdults>
        <numChildren>{$numChildren}</numChildren>
        <numGuests>{$numGuests}</numGuests>
        <sendResultsAs>{$sendResultsAs}</sendResultsAs>
        <photoFullSize>{$photoFullSize}</photoFullSize>
        <sortField>{$sortField}</sortField>
        <sortOrder>{$sortOrder}</sortOrder>
        </request>";
    }

    /**
     * Parser para busca multi-propriedades
     */
    private function parseMultiPropertySearchXml(string $xmlData, array $searchParams): array
    {

        try {
            $xml = new SimpleXMLElement($xmlData);
        } catch (\Exception $e) {
            Log::error('Erro ao fazer parse do XML de multi-property search', [
                'error' => $e->getMessage(),
                'xml_preview' => substr($xmlData, 0, 500)
            ]);

            return [
                'request' => $searchParams,
                'results' => [],
                'total_results' => 0,
                'timestamp' => now()->toISOString()
            ];
        }

        $xml = new SimpleXMLElement($xmlData);
        $results = [];

        // Verificar erros
        if (isset($xml->errorPresent) && (string) $xml->errorPresent === 'true') {
            return [
                'request' => $searchParams,
                'results' => [],
                'total_results' => 0,
                'timestamp' => now()->toISOString(),
                'error' => (string) $xml->errorMessage ?? 'Erro desconhecido'
            ];
        }

        // Pre-load ALL local properties once for reliable matching
        $allProperties = Property::where('is_active', true)->get();

        // Build multiple lookup maps for matching
        $byPropertyId = [];
        $byAddressFragment = [];
        $byHouseNumber = [];
        // Regex for street suffixes (abbreviated AND full-word forms)
        $streetSuffixRegex = '/(\d+\s+[\w\s]+(?:court|circle|drive|boulevard|lane|place|avenue|road|way|ct|dr|ln|blvd|cir|st|ave|rd|pl))\b/i';

        foreach ($allProperties as $prop) {
            $byPropertyId[$prop->property_id] = $prop;
            $addr = strtolower(trim(is_array($prop->address) ? json_encode($prop->address) : ($prop->address ?? '')));
            if (!empty($addr)) {
                // Extract house number from address for matching
                if (preg_match($streetSuffixRegex, $addr, $m)) {
                    $byAddressFragment[strtolower(trim($m[1]))] = $prop;
                }
                $byAddressFragment[$addr] = $prop;

                // Also index by just the house number for fallback
                if (preg_match('/^(\d+)/', $addr, $m)) {
                    $byHouseNumber[$m[1]] = $prop;
                }
            }
            // Index by alternate identifiers from title (e.g., "20-202" for property 3032)
            $title = strtolower($prop->title ?? '');
            if (preg_match_all('/(\d+-\d+)/', $title, $m)) {
                foreach ($m[1] as $alias) {
                    $byAddressFragment[$alias] = $prop;
                }
            }
        }

        if (isset($xml->MultiPropertySearchResult)) {

            foreach ($xml->MultiPropertySearchResult as $result) {
                // Extrair ID do Airbnb da URL
                $airbnbId = '';
                $propertyUrl = (string) $result->propertyWebsiteURL;
                if (preg_match('/\/rooms\/(\d+)/', $propertyUrl, $matches)) {
                    $airbnbId = $matches[1];
                }

                // Try to extract Bookerville property ID from bookingTargetURL
                $bookingTargetUrl = (string) $result->bookingTargetURL;
                $bookervilleId = null;
                if (preg_match('/bkvPropertyId=(\d+)/', $bookingTargetUrl, $m)) {
                    $bookervilleId = $m[1];
                }

                // Extrair preço
                $price = 0;
                $priceString = (string) $result->bookingPriceFrom;
                $cleanPrice = preg_replace('/[^\d.]/', '', $priceString);
                if (!empty($cleanPrice) && is_numeric($cleanPrice)) {
                    $price = PriceMarkupService::apply((float) $cleanPrice);
                }

                // Extrair endereço e cidade do propertyDisplayName
                $propertyName = (string) $result->propertyDisplayName ?? '';
                $address = '';
                $city = '';

                if (!empty($propertyName)) {
                    $parts = explode(', ', $propertyName);
                    if (count($parts) >= 2) {
                        $address = $parts[1] ?? '';
                        $city = $parts[0] ?? '';
                    } else {
                        $address = $propertyName;
                    }
                }

                // Match with local property using multiple strategies
                $propertyRecord = null;

                // Strategy 1: Match by Bookerville property ID (most reliable)
                if ($bookervilleId && isset($byPropertyId[$bookervilleId])) {
                    $propertyRecord = $byPropertyId[$bookervilleId];
                }

                // Strategy 2: Match by address fragment
                if (!$propertyRecord && !empty($address)) {
                    $addrLower = strtolower(trim($address));
                    if (isset($byAddressFragment[$addrLower])) {
                        $propertyRecord = $byAddressFragment[$addrLower];
                    } else {
                        // Try extracting house number + street from the search address
                        if (preg_match($streetSuffixRegex, $addrLower, $m)) {
                            $fragment = strtolower(trim($m[1]));
                            if (isset($byAddressFragment[$fragment])) {
                                $propertyRecord = $byAddressFragment[$fragment];
                            }
                        }
                    }
                    // Strategy 2b: Match by just house number (handles alternate address formats like 20-202)
                    if (!$propertyRecord) {
                        if (preg_match('/^(\d+[-\d]*)/', $addrLower, $m)) {
                            $houseNum = $m[1];
                            if (isset($byAddressFragment[$houseNum])) {
                                $propertyRecord = $byAddressFragment[$houseNum];
                            } elseif (isset($byHouseNumber[$houseNum])) {
                                $propertyRecord = $byHouseNumber[$houseNum];
                            }
                        }
                    }
                }

                // Strategy 3: Fallback to LIKE search on title/address
                if (!$propertyRecord && !empty($address)) {
                    $propertyRecord = Property::where('address', 'LIKE', "%{$address}%")
                        ->orWhere('title', 'LIKE', "%{$propertyName}%")
                        ->first();
                }

                $detailsForExtraction = $propertyRecord['details'] ?? [];
                $b_b = $this->extractBedroomsAndBathrooms($detailsForExtraction);
                $cityNew = $this->extractCity($detailsForExtraction);

                // Determine best image: local DB first, then Bookerville (skip placeholders)
                $bookervilleImage = (string) ($result->propertyMainPhotoURL ?? '');
                $isPlaceholder = empty($bookervilleImage)
                    || str_contains(strtolower($bookervilleImage), 'noprimaryphoto')
                    || str_contains(strtolower($bookervilleImage), 'placeholder');

                $mainImage = $propertyRecord->main_image ?? null;
                if (empty($mainImage) && !$isPlaceholder) {
                    $mainImage = $bookervilleImage;
                }
                // If still no image, try photos array
                if (empty($mainImage) && $propertyRecord) {
                    $photos = $propertyRecord->photos ?? [];
                    if (is_array($photos) && count($photos) > 0) {
                        $mainImage = $photos[0];
                    }
                }

                $results[] = [
                    'property_id' => $propertyRecord->property_id ?? $bookervilleId,
                    'airbnb_id' => $propertyRecord ? ($propertyRecord->airbnb_id ?? $airbnbId) : $airbnbId,
                    'property_name' => $propertyName,
                    'address' => $address,
                    'b_b' => $b_b,
                    'price' => $price,
                    'country' => 'US',
                    'city' => $cityNew ?? $city,
                    'property_type' => $propertyRecord->property_type ?? '',
                    'max_guests' => (int) $result->propertyMaxOccupants ?? 6,
                    'description' => (string) $result->propertyShortDescription ?? '',
                    'main_image' => $mainImage ?? '',
                    'photos' => $propertyRecord ? ($propertyRecord->photos ?? []) : [],
                    'booking_price' => $price,
                    'booking_message' => (string) $result->bookingMessage ?? '',
                    'booking_target_url' => $bookingTargetUrl,
                    'airbnb_url' => $propertyUrl,
                    'last_booked' => (string) $result->lastBooked ?? '',
                    'is_available' => (string) $result->bookingMessage === 'Ok'
                ];
            }
        }

        // Filter out unavailable properties - only return properties where bookingMessage === 'Ok'
        $availableResults = array_filter($results, function ($property) {
            return $property['is_available'] === true;
        });

        // Secondary validation: verify each property's availability using the per-property API
        // This catches cases where the bulk API returns 'Ok' but the property is actually booked
        $startDate = $searchParams['startDate'] ?? null;
        $endDate = $searchParams['endDate'] ?? null;

        if ($startDate && $endDate) {
            $verifiedResults = [];
            foreach ($availableResults as $property) {
                $propertyId = $property['property_id'] ?? null;
                if ($propertyId && $this->isDateRangeAvailable($propertyId, $startDate, $endDate)) {
                    $verifiedResults[] = $property;
                } else if (!$propertyId) {
                    // If no property ID, include it (can't verify)
                    $verifiedResults[] = $property;
                }
                // Properties that fail verification are silently excluded
            }
            $availableResults = $verifiedResults;
        }

        // Re-index array to ensure consecutive numeric keys
        $availableResults = array_values($availableResults);

        return [
            'request' => $searchParams,
            'results' => $availableResults,
            'total_results' => count($availableResults),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Validar credenciais da API
     */
    private function validateCredentials(): bool
    {
        return !empty($this->apiKey) && !empty($this->accountId);
    }

    /**
     * Check if a date range is truly available for a property by fetching BookedStays
     * from the per-property API and checking for overlaps, plus minimum nights validation.
     *
     * Uses the same logic as AvailabilityService::calculateAvailableDates() to ensure
     * consistency between search results and property detail calendar.
     *
     * @param string $propertyId Bookerville property ID
     * @param string $startDate Search start date (Y-m-d)
     * @param string $endDate Search end date (Y-m-d)
     * @return bool True if the date range is available, false if blocked
     */
    private function isDateRangeAvailable(string $propertyId, string $startDate, string $endDate): bool
    {
        try {
            // Calculate requested nights
            $requestedNights = (int) ((strtotime($endDate) - strtotime($startDate)) / 86400);

            if ($requestedNights < 1) {
                Log::info('Invalid date range - less than 1 night', [
                    'propertyId' => $propertyId,
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]);
                return false;
            }

            // Fetch BookedStays from per-property API
            $url = "{$this->baseUrl}/API-PropertyAvailability?s3cr3tK3y={$this->apiKey}&bkvPropertyId={$propertyId}";

            $response = Http::timeout(5)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/xml, text/xml, */*',
                    'User-Agent' => 'Laravel-Bookerville-Client/1.0'
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Failed to fetch property availability for validation', [
                    'propertyId' => $propertyId,
                    'status' => $response->status()
                ]);
                // If we can't verify, assume NOT available (fail closed for accuracy)
                return false;
            }

            $xmlData = $response->body();
            $xml = simplexml_load_string($xmlData);

            if ($xml === false) {
                Log::warning('Failed to parse property availability XML', [
                    'propertyId' => $propertyId
                ]);
                return false;
            }

            // Check for booked stays overlap
            if (isset($xml->BookedStays)) {
                $bookedStays = $xml->BookedStays->BookedStay ?? [];

                // Normalize to array
                if (!is_array($bookedStays) && !($bookedStays instanceof \Traversable)) {
                    $bookedStays = [$bookedStays];
                }

                // Convert booked stays to array for cross-reference
                $staysArray = [];
                foreach ($bookedStays as $stay) {
                    $status = strtolower((string) $stay->BookingStatus);
                    if ($status !== 'cancelled') {
                        $staysArray[] = [
                            'arrivalDate' => (string) $stay->ArrivalDate,
                            'departureDate' => (string) $stay->DepartureDate,
                            'confirmCode' => (string) $stay->ConfirmCode
                        ];
                    }
                }

                // Check each day in the requested range using same logic as AvailabilityService
                $currentDate = new \DateTime($startDate);
                $endDateObj = new \DateTime($endDate);

                while ($currentDate < $endDateObj) {
                    $dateStr = $currentDate->format('Y-m-d');
                    $checkDate = new \DateTime($dateStr);
                    $isBooked = false;

                    foreach ($staysArray as $stay) {
                        $arrival = new \DateTime($stay['arrivalDate']);
                        $departure = new \DateTime($stay['departureDate']);

                        // Check if this is arrival or departure day
                        $isArrivalDay = $checkDate == $arrival;
                        $isDepartureDay = $checkDate == $departure;

                        // Check for overlapping arrivals on departure day
                        if ($isDepartureDay) {
                            foreach ($staysArray as $otherStay) {
                                if ($otherStay['confirmCode'] === $stay['confirmCode']) continue;
                                $otherArrival = new \DateTime($otherStay['arrivalDate']);
                                if ($otherArrival == $departure) {
                                    $isBooked = true;
                                    break 2;
                                }
                            }
                        }

                        // Check for departure on arrival day
                        if ($isArrivalDay) {
                            foreach ($staysArray as $otherStay) {
                                if ($otherStay['confirmCode'] === $stay['confirmCode']) continue;
                                $otherDeparture = new \DateTime($otherStay['departureDate']);
                                if ($otherDeparture == $arrival) {
                                    $isBooked = true;
                                    break 2;
                                }
                            }
                        }

                        // Skip if only arrival day (without overlapping departure)
                        if ($isArrivalDay && !$isDepartureDay && !$isBooked) {
                            continue;
                        }

                        // Standard overlap check: arrival <= checkDate < departure
                        if ($checkDate >= $arrival && $checkDate < $departure) {
                            $isBooked = true;
                            break;
                        }
                    }

                    if ($isBooked) {
                        Log::info('Property unavailable - date is booked', [
                            'propertyId' => $propertyId,
                            'blockedDate' => $dateStr,
                            'searchStart' => $startDate,
                            'searchEnd' => $endDate
                        ]);
                        return false;
                    }

                    $currentDate->modify('+1 day');
                }
            }

            // Check minimum nights requirement from property details
            $detailsResponse = $this->getPropertyDetails(['propertyId' => $propertyId]);

            if ($detailsResponse['success'] && isset($detailsResponse['data'])) {
                $propertyData = $detailsResponse['data'];

                // Check rates for minimum stay during the requested period
                $rates = $propertyData['rates'] ?? [];
                $minStays = $propertyData['min_stays'] ?? [];

                $applicableMinNights = 1; // Default minimum

                // Check rate-based minimum stays first
                foreach ($rates as $rate) {
                    $rateStart = $rate['start_date'] ?? null;
                    $rateEnd = $rate['end_date'] ?? null;
                    $rateMinStay = (int) ($rate['minimum_stay'] ?? 1);

                    if ($rateStart && $rateEnd) {
                        // Check if the search dates fall within this rate period
                        if ($startDate >= $rateStart && $startDate <= $rateEnd) {
                            $applicableMinNights = max($applicableMinNights, $rateMinStay);
                        }
                    }
                }

                // Also check explicit min_stays periods
                foreach ($minStays as $minStay) {
                    $msStart = $minStay['start_date'] ?? null;
                    $msEnd = $minStay['end_date'] ?? null;
                    $msNights = (int) ($minStay['minimum_nights'] ?? 1);

                    if ($msStart && $msEnd) {
                        if ($startDate >= $msStart && $startDate <= $msEnd) {
                            $applicableMinNights = max($applicableMinNights, $msNights);
                        }
                    }
                }

                // Enforce minimum nights
                if ($requestedNights < $applicableMinNights) {
                    Log::info('Property unavailable - minimum nights not met', [
                        'propertyId' => $propertyId,
                        'requestedNights' => $requestedNights,
                        'minimumNights' => $applicableMinNights,
                        'startDate' => $startDate,
                        'endDate' => $endDate
                    ]);
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error checking date range availability', [
                'propertyId' => $propertyId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'error' => $e->getMessage()
            ]);
            // Fail closed for accuracy - if we can't verify, assume NOT available
            return false;
        }
    }

    /**
     * Limpar cache
     */
    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Obter ID da conta
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * Buscar propriedades por endereço usando LIKE
     *
     * @param string $address
     * @param int $limit
     * @return array
     */
    public function searchPropertiesByAddress(string $address, int $limit = 20, string $name): array
    {

        try {
            if (empty($address)) {
                return [
                    'success' => false,
                    'error' => 'EMPTY_ADDRESS',
                    'message' => 'Endereço não pode estar vazio'
                ];
            }

          $propertie = Property::where('address', 'LIKE', "%{$address}%")
                ->orWhere('title', 'LIKE', "%{$name}%")
                ->limit($limit)
                ->first();

            return [
                'success' => true,
                'data' => [
                    'properties' => $propertie,
                    'total_found' => $propertie,
                    'search_term' => $address,
                    'limit_applied' => $limit
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Erro ao buscar propriedades por endereço: " . $e->getMessage(), [
                'address' => $address,
                'limit' => $limit
            ]);

            return [
                'success' => false,
                'error' => 'SEARCH_ERROR',
                'message' => 'Erro ao buscar propriedades'
            ];
        }
    }

    /**
     * Buscar propriedades por endereço com filtros adicionais
     *
     * @param array $searchParams
     * @return array
     */
    public function searchPropertiesAdvanced(array $searchParams): array
    {
        try {
            $query = Property::query();

            // Filtro por endereço (obrigatório)
            if (isset($searchParams['address']) && !empty($searchParams['address'])) {
                $query->where('address', 'LIKE', "%{$searchParams['address']}%");
            } else {
                return [
                    'success' => false,
                    'error' => 'MISSING_ADDRESS',
                    'message' => 'Parâmetro address é obrigatório'
                ];
            }

            // Filtros opcionais
            if (isset($searchParams['city']) && !empty($searchParams['city'])) {
                $query->where('city', 'LIKE', "%{$searchParams['city']}%");
            }

            if (isset($searchParams['property_type']) && !empty($searchParams['property_type'])) {
                $query->where('property_type', $searchParams['property_type']);
            }

            if (isset($searchParams['max_guests']) && is_numeric($searchParams['max_guests'])) {
                $query->where('max_guests', '>=', (int) $searchParams['max_guests']);
            }

            if (isset($searchParams['min_price']) && is_numeric($searchParams['min_price'])) {
                $query->where('price', '>=', (float) $searchParams['min_price']);
            }

            if (isset($searchParams['max_price']) && is_numeric($searchParams['max_price'])) {
                $query->where('price', '<=', (float) $searchParams['max_price']);
            }

            // Limite de resultados
            $limit = $searchParams['limit'] ?? 20;
            $query->limit($limit);

            // Ordenação
            $orderBy = $searchParams['order_by'] ?? 'created_at';
            $orderDirection = $searchParams['order_direction'] ?? 'desc';
            $query->orderBy($orderBy, $orderDirection);

            $properties = $query->get();

            return [
                'success' => true,
                'data' => [
                    'properties' => $properties->toArray(),
                    'total_found' => $properties->count(),
                    'search_params' => $searchParams,
                    'filters_applied' => array_keys(array_filter($searchParams))
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Erro na busca avançada de propriedades: " . $e->getMessage(), [
                'search_params' => $searchParams
            ]);

            return [
                'success' => false,
                'error' => 'ADVANCED_SEARCH_ERROR',
                'message' => 'Erro na busca avançada'
            ];
        }
    }

    /**
     * Extrair quantidade de quartos e banheiros da coluna details
     *
     * @param mixed $details
     * @return array
     */
    public function extractBedroomsAndBathrooms($details): array
    {
        $result = [
            'bedrooms' => 0,
            'bathrooms' => 0
        ];

        try {
            // Se details é string JSON, decodificar
            if (is_string($details)) {
                $details = json_decode($details, true);
            }

            // Se details é array, extrair diretamente
            if (is_array($details)) {
                // Buscar bedrooms
                if (isset($details['bedrooms'])) {
                    $result['bedrooms'] = (int) $details['bedrooms'];
                }

                // Buscar bathrooms  
                if (isset($details['bathrooms'])) {
                    $result['bathrooms'] = (int) $details['bathrooms'];
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro ao extrair bedrooms/bathrooms: " . $e->getMessage(), [
                'details' => $details
            ]);
        }

        return $result;
    }

    /**
     * Extrair cidade da coluna details
     *
     * @param mixed $details
     * @return string
     */
    public function extractCity($details): string
    {
        try {
            // Se details é string JSON, decodificar
            if (is_string($details)) {
                $details = json_decode($details, true);
            }

            // Se details é array, extrair city
            if (is_array($details) && isset($details['city'])) {
                return (string) $details['city'];
            }
        } catch (\Exception $e) {
            Log::error("Erro ao extrair city: " . $e->getMessage(), [
                'details' => $details
            ]);
        }

        return '';
    }
}
