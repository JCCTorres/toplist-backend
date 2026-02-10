<?php

namespace App\Services;

use App\Models\Property;
use App\Models\ClientProperty;
use App\Services\BookervilleService;
use App\Services\ClientPropertyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Services\PriceMarkupService;
use Carbon\Carbon;

class SyncService
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
    private ClientPropertyService $clientPropertyService;

    public function __construct(
        BookervilleService $bookervilleService,
        ClientPropertyService $clientPropertyService
    ) {
        $this->bookervilleService = $bookervilleService;
        $this->clientPropertyService = $clientPropertyService;
    }

    /**
     * Sincroniza todas as propriedades da API do Bookerville para o banco MySQL
     */
    public function syncAllProperties(): array
    {
        try {
            Log::info('Iniciando sincronização de todas as propriedades');

            // 1. Buscar summary da API
            $summaryResponse = $this->bookervilleService->getPropertySummary();
            
            if (!$summaryResponse['success'] || !isset($summaryResponse['data'])) {
                throw new \Exception('Erro ao buscar summary da API');
            }

            $summaryProperties = $summaryResponse['data']['properties'] ?? [];
            
            Log::info('Encontradas ' . count($summaryProperties) . ' propriedades para sincronizar');

            // 2. Para cada propriedade, buscar details e salvar
            $successfulSyncs = [];
            $errors = [];

            foreach ($summaryProperties as $summaryProp) {
                try {
                    $result = $this->syncProperty($summaryProp);
                    if ($result) {
                        $successfulSyncs[] = $result;
                    }
                } catch (\Exception $error) {
                    Log::error("Erro ao sincronizar propriedade {$summaryProp['id']}: " . $error->getMessage());
                    $errors[] = [
                        'property_id' => $summaryProp['id'] ?? 'unknown',
                        'error' => $error->getMessage()
                    ];
                }
            }

            Log::info("Sincronização concluída: " . count($successfulSyncs) . " propriedades sincronizadas");

            return [
                'success' => true,
                'message' => "Sincronização concluída: " . count($successfulSyncs) . " propriedades",
                'count' => count($successfulSyncs),
                'errors' => $errors
            ];

        } catch (\Exception $error) {
            Log::error('Erro na sincronização: ' . $error->getMessage());
            return [
                'success' => false,
                'message' => "Erro na sincronização: " . $error->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Sincroniza uma propriedade específica
     */
    public function syncProperty(array $summaryData): ?Property
    {
        try {
            $propertyId = $summaryData['id'] ?? $summaryData['property_id'] ?? null;
            
            if (!$propertyId) {
                throw new \Exception('Property ID não encontrado nos dados de summary');
            }

            Log::info("Sincronizando propriedade: {$propertyId}");

            // 1. Buscar details da API (skip cache to ensure fresh rates)
            $detailsResponse = $this->bookervilleService->getPropertyDetails(['propertyId' => $propertyId, 'skipCache' => true]);
            
            if (!$detailsResponse['success'] || !isset($detailsResponse['data'])) {
                throw new \Exception("Erro ao buscar details da propriedade {$propertyId}");
            }

            $detailsData = $detailsResponse['data'];

            // 2. Determinar categoria (resort ou property)
            $category = $this->determineCategory($detailsData);

            // 3. Buscar título do cliente se existir
            $clientTitle = $this->getClientTitle($propertyId);

            // 4. Preparar dados para salvar
            $propertyData = [
                'property_id' => $propertyId,
                'title' => $clientTitle ?: $detailsData['name'] ?? $this->generateDefaultTitle($detailsData),
                'summary' => [
                    'property_id' => $propertyId,
                    'bkv_account_id' => $summaryData['bkvAccountId'] ?? '',
                    'manager_first_name' => $summaryData['managerFirstName'] ?? '',
                    'manager_last_name' => $summaryData['managerLastName'] ?? '',
                    'manager_phone' => $summaryData['managerPhone'] ?? '',
                    'business_name' => $summaryData['businessName'] ?? '',
                    'email_address_account' => $summaryData['emailAddressAccount'] ?? '',
                    'off_line' => $summaryData['offLine'] ?? false,
                    'last_update' => $summaryData['lastUpdate'] ?? '',
                    'property_details_api_url' => $summaryData['propertyDetailsApiUrl'] ?? ''
                ],
                'details' => [
                    'name' => $detailsData['name'] ?? '',
                    'address' => $detailsData['address'] ?? '',
                    'city' => $detailsData['address']['city'] ?? $detailsData['city'] ?? '',
                    'state' => $detailsData['address']['state'] ?? $detailsData['state'] ?? '',
                    'zip_code' => $detailsData['address']['zip_code'] ?? $detailsData['zipCode'] ?? '',
                    'country' => $detailsData['address']['country'] ?? $detailsData['country'] ?? '',
                    'property_type' => $detailsData['details']['property_type'] ?? $detailsData['propertyType'] ?? '',
                    'bedrooms' => (int) ($detailsData['details']['bedrooms'] ?? $detailsData['bedrooms'] ?? 0),
                    'bathrooms' => (int) ($detailsData['details']['bathrooms'] ?? $detailsData['bathrooms'] ?? 0),
                    'max_guests' => (int) ($detailsData['details']['max_occupancy'] ?? $detailsData['maxGuests'] ?? 0),
                    'description' => $detailsData['description'] ?? '',
                    'main_image' => $detailsData['photos'][0] ?? '',
                    'amenities' => $detailsData['amenities'] ?? [],
                    'photos' => $detailsData['photos'] ?? [],
                    'rates' => $detailsData['rates'] ?? [],
                    'fees' => $detailsData['fees'] ?? [],
                ],
                'category' => $category,
                'last_sync' => Carbon::now(),
                'is_active' => true
            ];

            // 5. Verificar se já existe
            $existingProperty = Property::where('property_id', $propertyId)->first();
            
            if ($existingProperty) {
                // Atualizar propriedade existente
                $existingProperty->update($propertyData);
                Log::info("Propriedade {$propertyId} atualizada");
                return $existingProperty->fresh();
            } else {
                // Criar nova propriedade
                $newProperty = Property::create($propertyData);
                Log::info("Nova propriedade {$propertyId} criada");
                return $newProperty;
            }

        } catch (\Exception $error) {
            Log::error("Erro ao sincronizar propriedade {$propertyId}: " . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Determina se uma propriedade é resort ou property
     */
    private function determineCategory(array $details): string
    {
        $resortKeywords = [
            'resort', 'hotel', 'complex', 'lodge', 'inn', 'spa',
            'club', 'village', 'community', 'estate'
        ];

        $name = strtolower($details['name'] ?? '');
        $propertyType = strtolower($details['propertyType'] ?? '');
        $businessName = strtolower($details['businessName'] ?? '');

        // Verificar se contém palavras-chave de resort
        $isResort = false;
        foreach ($resortKeywords as $keyword) {
            if (str_contains($name, $keyword) || 
                str_contains($propertyType, $keyword) || 
                str_contains($businessName, $keyword)) {
                $isResort = true;
                break;
            }
        }

        // Verificar se tem muitas amenidades (indicativo de resort)
        $hasManyAmenities = isset($details['amenities']) && count($details['amenities']) >= 10;

        // Verificar se tem muitos quartos (indicativo de resort)
        $hasManyBedrooms = isset($details['bedrooms']) && $details['bedrooms'] >= 5;

        return ($isResort || $hasManyAmenities || $hasManyBedrooms) ? 'resort' : 'property';
    }

    /**
     * Gera um título padrão baseado nos detalhes da propriedade
     */
    private function generateDefaultTitle(array $details): string
    {
        $bedrooms = $details['bedrooms'] ?? 0;
        $bathrooms = $details['bathrooms'] ?? 0;
        $city = $details['city'] ?? 'Unknown Location';
        
        return "{$bedrooms} Bedrooms / {$bathrooms} Baths / {$city}";
    }

    /**
     * Busca propriedades do banco
     */
    public function getProperties(array $query = []): Collection
    {
        try {
            $queryBuilder = Property::query();

            if (isset($query['category'])) {
                $queryBuilder->where('category', $query['category']);
            }

            if (isset($query['is_active'])) {
                $queryBuilder->where('is_active', $query['is_active']);
            }

            if (isset($query['limit'])) {
                $queryBuilder->limit($query['limit']);
            }

            return $queryBuilder->get();
        } catch (\Exception $error) {
            Log::error('Erro ao buscar propriedades: ' . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Busca propriedades por categoria
     */
    public function getPropertiesByCategory(string $category, int $limit = 10): Collection
    {
        try {
            return Property::where('category', $category)
                ->where('is_active', true)
                ->limit($limit)
                ->get();
        } catch (\Exception $error) {
            Log::error("Erro ao buscar propriedades por categoria {$category}: " . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Busca propriedades por categoria com imagens válidas
     * Se uma propriedade não tem imagem, busca a próxima até encontrar o limite solicitado
     */
    public function getPropertiesByCategoryWithImages(string $category, int $limit = 10): Collection
    {
        try {
        
            $allProperties = Property::where('category', $category)
                ->where('is_active', 1)
                ->get();
     
            $propertiesWithImages = collect();
            
            // Buscar propriedades até encontrar o limite solicitado ou esgotar todas as opções
            foreach ($allProperties as $property) {
                
                if ($propertiesWithImages->count() >= $limit) {
                    break;
                }
                
                // Verificar se a propriedade tem imagem válida
        
                if ($this->hasValidImage($property)) {
                 
                    $propertiesWithImages->push($property);
                }
            }
            
            return $propertiesWithImages;
        } catch (\Exception $error) {
            Log::error("Erro ao buscar propriedades por categoria {$category} com imagens: " . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Verifica se uma propriedade tem uma imagem válida
     */
    private function hasValidImage(Property $property): bool
    {
        try {
            // Verificar se existe imagem principal
            $details = $property->details;
         
            $mainImage = $details['mainImage'] ?? $details['main_image'] ?? '';
         
            if (empty($mainImage) || $mainImage === 'null' || $mainImage === 'undefined') {
                return false;
            }
            
            // Verificar se é uma URL válida (http/https)
            if (filter_var($mainImage, FILTER_VALIDATE_URL)) {
                return true;
            }
   
            // Se não for uma URL válida, verificar se pelo menos tem um formato de imagem
            $imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
            foreach ($imageExtensions as $ext) {
                if (str_contains(strtolower($mainImage), $ext)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $error) {
            Log::error("Erro ao verificar imagem da propriedade {$property->property_id}: " . $error->getMessage());
            return false;
        }
    }

    /**
     * Atualiza uma propriedade específica
     */
    public function updateProperty(string $propertyId, array $updateData): ?Property
    {
        try {
            $property = Property::where('property_id', $propertyId)->first();
            
            if ($property) {
                $property->update($updateData);
                return $property->fresh();
            }
            
            return null;
        } catch (\Exception $error) {
            Log::error("Erro ao atualizar propriedade {$propertyId}: " . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Remove uma propriedade (soft delete)
     */
    public function deactivateProperty(string $propertyId): ?Property
    {
        try {
            $property = Property::where('property_id', $propertyId)->first();
            
            if ($property) {
                $property->update(['is_active' => false]);
                return $property->fresh();
            }
            
            return null;
        } catch (\Exception $error) {
            Log::error("Erro ao desativar propriedade {$propertyId}: " . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Busca o título da propriedade na tabela client_properties
     */
    public function getClientTitle(string $propertyId): ?string
    {
        try {
            $airbnbId = (int) $propertyId;
            
            if ($airbnbId === 0) {
                return null;
            }
            
            $clientProperty = ClientProperty::where('airbnb_id', $airbnbId)->first();
            return $clientProperty?->title;
        } catch (\Exception $error) {
            Log::error("Erro ao buscar título do cliente para propriedade {$propertyId}: " . $error->getMessage());
            return null;
        }
    }

    /**
     * Busca o título da propriedade na tabela client_properties pelo endereço
     */
    public function getClientTitleByAddress(string $address): ?string
    {
        try {
            if (empty($address)) {
                return null;
            }
            
            $clientProperty = ClientProperty::where('address', 'LIKE', "%{$address}%")->first();
            return $clientProperty?->title;
        } catch (\Exception $error) {
            Log::error("Erro ao buscar título do cliente para endereço {$address}: " . $error->getMessage());
            return null;
        }
    }

    /**
     * Busca a propriedade completa na tabela client_properties pelo endereço
     */
    public function getClientPropertyByAddress(string $address): ?ClientProperty
    {
        try {
            if (empty($address)) {
                return null;
            }
            
            // Buscar com LIKE para encontrar endereços similares
            $clientProperty = ClientProperty::where('address', 'LIKE', "%{$address}%")->first();
            
            if (!$clientProperty) {
                // Tentar busca exata se não encontrou com LIKE
                $clientProperty = ClientProperty::where('address', $address)->first();
            }
            
            return $clientProperty;
        } catch (\Exception $error) {
            Log::error("Erro ao buscar propriedade do cliente para endereço {$address}: " . $error->getMessage());
            return null;
        }
    }

    /**
     * Sincroniza uma propriedade específica por ID
     */
    public function syncPropertyById(string $propertyId): array
    {
        try {
            // Primeiro buscar o summary para obter os dados básicos
            $summaryResponse = $this->bookervilleService->getPropertySummary();
            
            if (!$summaryResponse['success'] || !isset($summaryResponse['data']['properties'])) {
                throw new \Exception('Erro ao buscar dados de summary');
            }

            // Encontrar a propriedade específica no summary
            $summaryProperty = null;
            foreach ($summaryResponse['data']['properties'] as $prop) {
                if (($prop['id'] ?? $prop['property_id'] ?? '') === $propertyId) {
                    $summaryProperty = $prop;
                    break;
                }
            }

            if (!$summaryProperty) {
                throw new \Exception("Propriedade {$propertyId} não encontrada no summary");
            }

            // Sincronizar a propriedade
            $result = $this->syncProperty($summaryProperty);
            
            return [
                'success' => true,
                'message' => "Propriedade {$propertyId} sincronizada com sucesso",
                'data' => $result
            ];

        } catch (\Exception $error) {
            Log::error("Erro ao sincronizar propriedade {$propertyId}: " . $error->getMessage());
            return [
                'success' => false,
                'message' => $error->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtém estatísticas de sincronização
     */
    public function getSyncStats(): array
    {
        try {
            $totalProperties = Property::count();
            $activeProperties = Property::where('is_active', true)->count();
            $resorts = Property::where('category', 'resort')->where('is_active', true)->count();
            $properties = Property::where('category', 'property')->where('is_active', true)->count();
            $recentlySynced = Property::where('last_sync', '>=', Carbon::now()->subHour())->count();

            return [
                'total_properties' => $totalProperties,
                'active_properties' => $activeProperties,
                'resorts' => $resorts,
                'properties' => $properties,
                'recently_synced' => $recentlySynced,
                'last_sync_time' => Property::max('last_sync')
            ];
        } catch (\Exception $error) {
            Log::error('Erro ao obter estatísticas de sincronização: ' . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Busca todas as propriedades formatadas como cards para o frontend
     */
    public function getAllPropertiesCards(string $category = null, int $limit = null): array
    {
        try {
            Log::info("Buscando propriedades cards - Categoria: " . ($category ?? 'all') . ", Limite: " . ($limit ?? 'none'));

            $properties = collect();
           
            if ($category && in_array($category, ['resort', 'property'])) {
                // Buscar por categoria específica
                $properties = $this->getPropertiesByCategory($category, $limit ?? 100);
            } else {
                // Buscar todas as propriedades
                $query = ['is_active' => true];
                if ($limit) {
                    $query['limit'] = $limit;
                }
                $properties = $this->getProperties($query);
                
                if ($limit && $properties->count() > $limit) {
                    $properties = $properties->take($limit);
                }
            }

            // Formatar dados para o frontend (mesmo formato dos home cards)
            $allCards = $properties->map(function ($property) {
                return $this->formatPropertyCard($property);
            });

            Log::info("Cards formatados: " . $allCards->count() . " propriedades");

            return [
                'success' => true,
                'data' => [
                    'properties' => $allCards->values(),
                    'count' => $allCards->count(),
                    'category' => $category ?? 'all',
                    'timestamp' => Carbon::now()->toISOString()
                ]
            ];

        } catch (\Exception $error) {
            Log::error('Erro ao buscar todas as propriedades cards: ' . $error->getMessage());
            
            return [
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as propriedades'
            ];
        }
    }

    /**
     * Formata uma propriedade como card para o frontend
     */
    private function formatPropertyCard(Property $property): array
    {
        try {
            $details = $property->details ?? [];
            
            // Verificar se é uma propriedade do Bookerville (tem campos específicos)
            $isBookervilleProperty = $property->source === 'bookerville';
            // Verificar se é uma propriedade do Airbnb
            $isAirbnbProperty = $property->source === 'airbnb';
            
            if ($isBookervilleProperty) {
                
                // Propriedade do Bookerville - retornar TODOS os dados
                return [
                    // Identificação
                    'id' => $property->property_id,
                    'bookerville_id' => $property->bookerville_id,
                    'bkv_account_id' => $property->bkv_account_id,
                    'airbnb_id' => $property->airbnb_id ? (string) $property->airbnb_id : (self::AIRBNB_ID_MAPPING[$property->property_id] ?? null),

                    // Informações básicas
                    'title' => $property->title,
                    'name' => $details['name'] ?? '',
                    'description' => $property->description,
                    
                    // Localização
                    'address' => $property->address,
                    'city' => $details['city'] ?? '',
                    'state' => $details['state'] ?? '',
                    'zip_code' => $property->zip_code,
                    'country' => $property->country,
                    
                    // Características da propriedade
                    'property_type' => $property->property_type,
                    'bedrooms' => $details['bedrooms'] ?? 0,
                    'bathrooms' => $details['bathrooms'] ?? 0,
                    'max_guests' => $property->max_guests,
                    'category' => $property->category,
                    
                    // Imagens
                    'main_image' => $property->main_image,
                    'photos' => $property->photos ?? [],
                    
                    // Comodidades
                    'amenities' => $property->amenities ?? [],
                    
                    // Informações do gerente
                    'manager_first_name' => $property->manager_first_name,
                    'manager_last_name' => $property->manager_last_name,
                    'manager_phone' => $property->manager_phone,
                    'business_name' => $property->business_name,
                    'email_address_account' => $property->email_address_account,
                    
                    // Status e configurações
                    'is_active' => $property->is_active,
                    'off_line' => $property->off_line,
                    'property_details_api_url' => $property->property_details_api_url,
                    
                    // Timestamps
                    'last_sync' => $property->last_sync?->toISOString(),
                    'bookerville_last_update' => $property->bookerville_last_update?->toISOString(),
                    'bookerville_created_at' => $property->bookerville_created_at?->toISOString(),
                    'bookerville_updated_at' => $property->bookerville_updated_at?->toISOString(),
                    'created_at' => $property->created_at?->toISOString(),
                    'updated_at' => $property->updated_at?->toISOString(),
                    
                    // Dados estruturados
                    'summary_data' => $property->summary_data,
                    'details' => $property->details,
                    'original_data' => $property->original_data,
                    
                    // Campos calculados para compatibilidade
                    'guests' => $this->formatGuestInfo([
                        'max_guests' => $property->max_guests,
                        'bedrooms' => $details['bedrooms'] ?? 0,
                        'bathrooms' => $details['bathrooms'] ?? 0
                    ]),
                    'subtitle' => ($details['name'] ?? '') . ($property->property_type ? ' • ' . $property->property_type : ''),
                    'image' => $property->main_image_url ?? $property->main_image,
                    'nightly_rate' => $this->extractNightlyRate($details),
                    'rates' => $details['rates'] ?? [],
                    'source' => 'bookerville'
                ];
                
            } elseif ($isAirbnbProperty) {
                // Propriedade do Airbnb - retornar todos os dados disponíveis
                $address = $property->address ?? '';
                $city = '';
                $state = '';
                
                // Tentar extrair cidade e estado do endereço
                if (!empty($address)) {
                    $addressParts = explode(',', $address);
                    if (count($addressParts) >= 2) {
                        $city = trim($addressParts[count($addressParts) - 2] ?? '');
                        $state = trim($addressParts[count($addressParts) - 1] ?? '');
                    }
                }
                
                // Extrair dados do título para inferir quartos/hóspedes
                $guestInfo = $this->extractGuestInfoFromTitle($property->title);
                
                return [
                    // Identificação
                    'id' => $property->property_id,
                    'airbnb_id' => $property->airbnb_id,
                    'airbnb_url' => $property->airbnb_url,
                    
                    // Informações básicas
                    'title' => $property->title,
                    'description' => $property->description,
                    
                    // Localização
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'house_number' => $property->house_number,
                    
                    // Características inferidas
                    'bedrooms' => $guestInfo['bedrooms'],
                    'bathrooms' => $guestInfo['bathrooms'],
                    'max_guests' => $guestInfo['guests'],
                    'category' => $property->category,
                    
                    // Informações específicas do Airbnb
                    'owner' => $property->owner,
                    'observations' => $property->observations,
                    
                    // Imagens
                    'main_image' => $property->main_image,
                    
                    // Status
                    'is_active' => $property->is_active,
                    
                    // Timestamps
                    'last_sync' => $property->last_sync?->toISOString(),
                    'created_at' => $property->created_at?->toISOString(),
                    'updated_at' => $property->updated_at?->toISOString(),
                    
                    // Dados estruturados
                    'original_data' => $property->original_data,
                    
                    // Campos calculados para compatibilidade
                    'guests' => $this->formatGuestInfo([
                        'max_guests' => $guestInfo['guests'],
                        'bedrooms' => $guestInfo['bedrooms'],
                        'bathrooms' => $guestInfo['bathrooms']
                    ]),
                    'subtitle' => $property->owner ? "Anfitrião: {$property->owner}" : 'Propriedade Airbnb',
                    'image' => $property->main_image,
                    'source' => 'airbnb'
                ];
                
            } else {
                // Propriedades legadas (client_properties)
                $addressData = $details['address'] ?? $details;
                $address = '';
                $city = '';
                $state = '';
                
                if (is_array($addressData)) {
                    $address = ($addressData['street'] ?? '') . ', ' . ($addressData['city'] ?? '') . ', ' . ($addressData['state'] ?? '');
                    $city = $addressData['city'] ?? '';
                    $state = $addressData['state'] ?? '';
                } else {
                    $address = (string) $addressData;
                }
                
                return [
                    'id' => $property->property_id,
                    'title' => $property->title ?: 'Propriedade sem título',
                    'subtitle' => $property->category ?? '',
                    'guests' => $this->formatGuestInfo([
                        'max_guests' => $property->max_guests ?? 0,
                        'bedrooms' => $property->bedrooms ?? 0,
                        'bathrooms' => $property->bathrooms ?? 0
                    ]),
                    'image' => $property->main_image ?? '',
                    'category' => $property->category,
                    'city' => $city,
                    'state' => $state,
                    'address' => $address,
                    'bedrooms' => $property->bedrooms ?? 0,
                    'bathrooms' => $property->bathrooms ?? 0,
                    'max_guests' => $property->max_guests ?? 0,
                    'last_sync' => $property->last_sync?->toISOString(),
                    'is_active' => $property->is_active,
                    'source' => $property->source ?? 'legacy'
                ];
            }
            
        } catch (\Exception $error) {
            Log::error("Erro ao formatar card da propriedade {$property->property_id}: " . $error->getMessage());
            
            return [
                'id' => $property->property_id,
                'title' => 'Erro ao carregar propriedade',
                'subtitle' => 'Erro ao carregar detalhes',
                'guests' => '',
                'image' => '',
                'category' => $property->category ?? 'unknown',
                'city' => '',
                'state' => '',
                'address' => '',
                'bedrooms' => 0,
                'bathrooms' => 0,
                'max_guests' => 0,
                'last_sync' => $property->last_sync?->toISOString(),
                'is_active' => $property->is_active ?? false,
                'source' => $property->source ?? 'error',
                'error' => $error->getMessage()
            ];
        }
    }

    /**
     * Extrai informações de hóspedes/quartos/banheiros do título da propriedade
     */
    private function extractGuestInfoFromTitle(string $title): array
    {
        $bedrooms = 0;
        $bathrooms = 0;
        $guests = 0;
        
        // Padrões para extrair números do título
        if (preg_match('/(\d+)\s*br/i', $title, $matches)) {
            $bedrooms = (int) $matches[1];
        }
        
        if (preg_match('/(\d+)\s*ba/i', $title, $matches)) {
            $bathrooms = (int) $matches[1];
        }
        
        if (preg_match('/(\d+)\s*(guests?|people|persons?)/i', $title, $matches)) {
            $guests = (int) $matches[1];
        }
        
        // Se não encontrou hóspedes, estimar baseado nos quartos (média de 2 por quarto)
        if ($guests === 0 && $bedrooms > 0) {
            $guests = $bedrooms * 2;
        }
        
        return [
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'guests' => $guests
        ];
    }

    /**
     * Formata as informações de hóspedes/quartos/banheiros
     */
    private function extractNightlyRate(array $details): ?float
    {
        $rates = $details['rates'] ?? [];
        $nightlyRate = null;

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

        return $nightlyRate !== null ? PriceMarkupService::apply($nightlyRate) : null;
    }

    private function formatGuestInfo(array $details): string
    {
        $maxGuests = $details['max_guests'] ?? 0;
        $bedrooms = $details['bedrooms'] ?? 0;
        $bathrooms = $details['bathrooms'] ?? 0;
        
        $parts = [];
        
        if ($maxGuests > 0) {
            $parts[] = "{$maxGuests} guests";
        }
        
        if ($bedrooms > 0) {
            $parts[] = "{$bedrooms} beds";
        }
        
        if ($bathrooms > 0) {
            $parts[] = "{$bathrooms} baths";
        }
        
        return implode(' • ', $parts);
    }
}