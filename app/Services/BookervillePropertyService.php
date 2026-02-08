<?php

namespace App\Services;

use App\Models\BookervilleProperty;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class BookervillePropertyService
{
    private string $baseUrl = 'https://api.bookerville.com/api/v1';
    private int $cacheTime = 3600; // 1 hour default cache
    
    /**
     * Buscar propriedades do summary (listagem)
     */
    public function fetchSummary(string $accountId): array
    {
        $cacheKey = "bookerville_summary_{$accountId}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($accountId) {
            try {
                $response = Http::timeout(30)->get("{$this->baseUrl}/properties/{$accountId}/summary");
                
                if (!$response->successful()) {
                    Log::error("Bookerville API error for account {$accountId}", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return [];
                }
                
                return $this->parseXmlResponse($response->body());
                
            } catch (\Exception $e) {
                Log::error("Error fetching Bookerville summary for account {$accountId}", [
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Buscar detalhes de uma propriedade específica
     */
    public function fetchDetails(string $accountId, string $propertyId): array
    {
        $cacheKey = "bookerville_details_{$accountId}_{$propertyId}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($accountId, $propertyId) {
            try {
                $response = Http::timeout(30)->get("{$this->baseUrl}/properties/{$accountId}/{$propertyId}");
                
                if (!$response->successful()) {
                    Log::error("Bookerville API error for property {$propertyId}", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return [];
                }
                
                return $this->parseXmlResponse($response->body());
                
            } catch (\Exception $e) {
                Log::error("Error fetching Bookerville details for property {$propertyId}", [
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

    /**
     * Parse XML response para array PHP
     */
    public function parseXmlResponse(string $xmlString): array
    {
        try {
            // Limpar XML para evitar problemas de encoding
            $xmlString = trim($xmlString);
            $xmlString = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $xmlString);
            
            $xml = new SimpleXMLElement($xmlString);
            return $this->xmlToArray($xml);
            
        } catch (\Exception $e) {
            Log::error('Error parsing XML response', [
                'error' => $e->getMessage(),
                'xml_length' => strlen($xmlString)
            ]);
            return [];
        }
    }

    /**
     * Converter SimpleXMLElement para array PHP
     */
    private function xmlToArray(SimpleXMLElement $xml): array
    {
        $array = json_decode(json_encode($xml), true);
        
        // Normalizar arrays simples
        return $this->normalizeArray($array);
    }

    /**
     * Normalizar estrutura de arrays vindos do XML
     */
    private function normalizeArray($data): array
    {
        if (!is_array($data)) {
            return [];
        }
        
        $normalized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = $this->normalizeArray($value);
            } else {
                $normalized[$key] = $value;
            }
        }
        
        return $normalized;
    }

    /**
     * Sincronizar propriedades do summary
     */
    public function syncSummary(string $accountId): array
    {
        $summaryData = $this->fetchSummary($accountId);
        
        if (empty($summaryData)) {
            return [
                'success' => false,
                'message' => 'No data received from Bookerville API',
                'synced' => 0
            ];
        }
        
        $synced = 0;
        $errors = [];
        
        // Processar propriedades do summary
        $properties = $this->extractPropertiesFromSummary($summaryData);
        
        foreach ($properties as $propertyData) {
            try {
                $this->createOrUpdateFromSummary($accountId, $propertyData);
                $synced++;
            } catch (\Exception $e) {
                $propertyId = $propertyData['property_id'] ?? 'unknown';
                $errors[] = "Property {$propertyId}: {$e->getMessage()}";
                Log::error('Error syncing property', [
                    'property_data' => $propertyData,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'success' => true,
            'synced' => $synced,
            'errors' => $errors,
            'total_properties' => count($properties)
        ];
    }

    /**
     * Sincronizar detalhes de propriedades específicas
     */
    public function syncDetails(string $accountId, ?array $propertyIds = null): array
    {
        $query = BookervilleProperty::where('account_id', $accountId);
        
        if ($propertyIds) {
            $query->whereIn('property_id', $propertyIds);
        } else {
            // Sincronizar apenas propriedades que precisam de atualização
            $query->needsDetailsSync(24);
        }
        
        $properties = $query->get();
        $synced = 0;
        $errors = [];
        
        foreach ($properties as $property) {
            try {
                $detailsData = $this->fetchDetails($accountId, $property->property_id);
                
                if (!empty($detailsData)) {
                    $this->updateFromDetails($property, $detailsData);
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors[] = "Property {$property->property_id}: {$e->getMessage()}";
                Log::error('Error syncing property details', [
                    'property_id' => $property->property_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'success' => true,
            'synced' => $synced,
            'errors' => $errors,
            'total_properties' => $properties->count()
        ];
    }

    /**
     * Extrair propriedades do XML de summary
     */
    private function extractPropertiesFromSummary(array $summaryData): array
    {
        $properties = [];
        
        // Diferentes estruturas possíveis do XML
        if (isset($summaryData['properties']['property'])) {
            $propertyList = $summaryData['properties']['property'];
            
            // Se é um único item, transformar em array
            if (isset($propertyList['property_id'])) {
                $propertyList = [$propertyList];
            }
            
            $properties = $propertyList;
        } elseif (isset($summaryData['property'])) {
            $properties = is_array($summaryData['property'][0]) ? 
                $summaryData['property'] : [$summaryData['property']];
        }
        
        return $properties;
    }

    /**
     * Criar ou atualizar propriedade a partir do summary
     */
    private function createOrUpdateFromSummary(string $accountId, array $propertyData): BookervilleProperty
    {
        $propertyId = $propertyData['property_id'] ?? $propertyData['id'] ?? null;
        
        if (!$propertyId) {
            throw new \Exception('Property ID not found in data');
        }
        
        $data = $this->mapSummaryData($accountId, $propertyData);
        
        return BookervilleProperty::updateOrCreate(
            ['property_id' => $propertyId, 'account_id' => $accountId],
            $data
        );
    }

    /**
     * Atualizar propriedade com dados de detalhes
     */
    private function updateFromDetails(BookervilleProperty $property, array $detailsData): BookervilleProperty
    {
        $data = $this->mapDetailsData($detailsData);
        $data['last_details_sync'] = now();
        $data['raw_details_data'] = json_encode($detailsData);
        
        $property->update($data);
        
        return $property;
    }

    /**
     * Mapear dados do summary para o modelo
     */
    private function mapSummaryData(string $accountId, array $data): array
    {
        return [
            'account_id' => $accountId,
            'property_id' => $data['property_id'] ?? $data['id'],
            'name' => $data['name'] ?? $data['property_name'] ?? '',
            'address' => $data['address'] ?? $data['street_address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? $data['state_province'] ?? '',
            'zip_code' => $data['zip_code'] ?? $data['postal_code'] ?? '',
            'country' => $data['country'] ?? 'US',
            'property_type' => $data['property_type'] ?? $data['type'] ?? '',
            'bedrooms' => (int) ($data['bedrooms'] ?? $data['bedroom_count'] ?? 0),
            'bathrooms' => (float) ($data['bathrooms'] ?? $data['bathroom_count'] ?? 0),
            'max_guests' => (int) ($data['max_guests'] ?? $data['sleeps'] ?? 0),
            'description' => $data['description'] ?? $data['short_description'] ?? '',
            'amenities' => $this->parseAmenities($data),
            'images' => $this->parseImages($data),
            'manager' => $this->parseManager($data),
            'off_line' => $this->parseBoolean($data['off_line'] ?? $data['offline'] ?? false),
            'details_url' => $data['details_url'] ?? '',
            'raw_summary_data' => json_encode($data),
            'last_summary_sync' => now()
        ];
    }

    /**
     * Mapear dados de detalhes para o modelo
     */
    private function mapDetailsData(array $data): array
    {
        return [
            'description' => $data['description'] ?? $data['long_description'] ?? '',
            'amenities' => $this->parseAmenities($data),
            'images' => $this->parseImages($data),
            'booking_info' => $this->parseBookingInfo($data),
            'availability' => $this->parseAvailability($data),
            'external_links' => $this->parseExternalLinks($data),
            'manager' => $this->parseManager($data)
        ];
    }

    /**
     * Parse amenities do XML
     */
    private function parseAmenities(array $data): array
    {
        $amenities = [];
        
        if (isset($data['amenities'])) {
            if (is_array($data['amenities'])) {
                if (isset($data['amenities']['amenity'])) {
                    $amenityList = $data['amenities']['amenity'];
                    if (is_string($amenityList)) {
                        $amenities[] = $amenityList;
                    } else {
                        $amenities = array_values($amenityList);
                    }
                } else {
                    $amenities = array_values($data['amenities']);
                }
            } else {
                $amenities = explode(',', $data['amenities']);
            }
        }
        
        return array_map('trim', $amenities);
    }

    /**
     * Parse images do XML
     */
    private function parseImages(array $data): array
    {
        $images = [];
        
        if (isset($data['images']['image'])) {
            $imageList = $data['images']['image'];
            
            if (is_string($imageList)) {
                $images[] = $imageList;
            } elseif (is_array($imageList)) {
                foreach ($imageList as $image) {
                    if (is_string($image)) {
                        $images[] = $image;
                    } elseif (isset($image['url'])) {
                        $images[] = $image['url'];
                    }
                }
            }
        } elseif (isset($data['photos'])) {
            $images = is_array($data['photos']) ? $data['photos'] : [$data['photos']];
        }
        
        return $images;
    }

    /**
     * Parse manager information
     */
    private function parseManager(array $data): array
    {
        $manager = [];
        
        if (isset($data['manager'])) {
            $managerData = $data['manager'];
            
            $manager = [
                'firstName' => $managerData['first_name'] ?? $managerData['firstName'] ?? '',
                'lastName' => $managerData['last_name'] ?? $managerData['lastName'] ?? '',
                'businessName' => $managerData['business_name'] ?? $managerData['businessName'] ?? '',
                'phone' => $managerData['phone'] ?? $managerData['phone_number'] ?? '',
                'email' => $managerData['email'] ?? $managerData['email_address'] ?? ''
            ];
        }
        
        return $manager;
    }

    /**
     * Parse booking information
     */
    private function parseBookingInfo(array $data): array
    {
        $bookingInfo = [];
        
        if (isset($data['booking'])) {
            $booking = $data['booking'];
            
            $bookingInfo = [
                'minStay' => (int) ($booking['min_stay'] ?? $booking['minimum_stay'] ?? 1),
                'maxStay' => (int) ($booking['max_stay'] ?? $booking['maximum_stay'] ?? 0),
                'checkInTime' => $booking['check_in_time'] ?? $booking['checkin_time'] ?? '',
                'checkOutTime' => $booking['check_out_time'] ?? $booking['checkout_time'] ?? '',
                'baseRate' => (float) ($booking['base_rate'] ?? $booking['nightly_rate'] ?? 0),
                'currency' => $booking['currency'] ?? 'USD'
            ];
        }
        
        return $bookingInfo;
    }

    /**
     * Parse availability information
     */
    private function parseAvailability(array $data): array
    {
        $availability = [
            'availableDates' => [],
            'blockedDates' => [],
            'lastUpdated' => now()->toISOString()
        ];
        
        if (isset($data['availability'])) {
            $avail = $data['availability'];
            
            if (isset($avail['available_dates'])) {
                $availability['availableDates'] = is_array($avail['available_dates']) ? 
                    $avail['available_dates'] : [$avail['available_dates']];
            }
            
            if (isset($avail['blocked_dates'])) {
                $availability['blockedDates'] = is_array($avail['blocked_dates']) ? 
                    $avail['blocked_dates'] : [$avail['blocked_dates']];
            }
        }
        
        return $availability;
    }

    /**
     * Parse external links
     */
    private function parseExternalLinks(array $data): array
    {
        $links = [];
        
        if (isset($data['external_links'])) {
            $linkData = $data['external_links'];
            
            if (is_array($linkData)) {
                $links = $linkData;
            }
        }
        
        return $links;
    }

    /**
     * Parse boolean values
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_string($value)) return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        if (is_numeric($value)) return (bool) $value;
        
        return false;
    }

    /**
     * Limpar cache para uma conta específica
     */
    public function clearCache(string $accountId): void
    {
        Cache::forget("bookerville_summary_{$accountId}");
        
        // Limpar cache de detalhes de todas as propriedades da conta
        $properties = BookervilleProperty::where('account_id', $accountId)->pluck('property_id');
        
        foreach ($properties as $propertyId) {
            Cache::forget("bookerville_details_{$accountId}_{$propertyId}");
        }
    }

    /**
     * Definir tempo de cache
     */
    public function setCacheTime(int $seconds): self
    {
        $this->cacheTime = $seconds;
        return $this;
    }

    /**
     * Buscar propriedades com filtros
     */
    public function getProperties(array $filters = []): Collection
    {
        $query = BookervilleProperty::query();
        
        if (isset($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }
        
        if (isset($filters['city'])) {
            $query->byCity($filters['city']);
        }
        
        if (isset($filters['state'])) {
            $query->byState($filters['state']);
        }
        
        if (isset($filters['property_type'])) {
            $query->byPropertyType($filters['property_type']);
        }
        
        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->active();
        }
        
        if (isset($filters['needs_sync']) && $filters['needs_sync']) {
            $query->needsDetailsSync();
        }
        
        return $query->get();
    }
}