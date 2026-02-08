<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookervilleProperty extends Model
{
    use HasFactory;

    protected $table = 'bookerville_properties';

    protected $fillable = [
        'property_id',
        'account_id',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'property_type',
        'bedrooms',
        'bathrooms',
        'max_guests',
        'description',
        'amenities',
        'images',
        'booking_info',
        'availability',
        'external_links',
        'manager',
        'off_line',
        'details_url',
        'raw_summary_data',
        'raw_details_data',
        'last_summary_sync',
        'last_details_sync',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'booking_info' => 'array',
        'availability' => 'array',
        'external_links' => 'array',
        'manager' => 'array',
        'off_line' => 'boolean',
        'bedrooms' => 'integer',
        'bathrooms' => 'decimal:1',
        'max_guests' => 'integer',
        'last_summary_sync' => 'datetime',
        'last_details_sync' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'property_id';
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('off_line', false);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByPropertyType($query, $type)
    {
        return $query->where('property_type', $type);
    }

    public function scopeNeedsSummarySync($query, $hours = 24)
    {
        return $query->where(function ($q) use ($hours) {
            $q->whereNull('last_summary_sync')
              ->orWhere('last_summary_sync', '<', now()->subHours($hours));
        });
    }

    public function scopeNeedsDetailsSync($query, $hours = 24)
    {
        return $query->where(function ($q) use ($hours) {
            $q->whereNull('last_details_sync')
              ->orWhere('last_details_sync', '<', now()->subHours($hours));
        });
    }

    /**
     * Accessors & Mutators
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    public function getManagerNameAttribute(): string
    {
        if (!$this->manager) return '';
        
        $firstName = $this->manager['firstName'] ?? $this->manager['first_name'] ?? '';
        $lastName = $this->manager['lastName'] ?? $this->manager['last_name'] ?? '';
        
        return trim("{$firstName} {$lastName}");
    }

    /**
     * Converter para formato TypeScript compatível
     */
    public function toTypeScriptFormat(): array
    {
        return [
            'id' => $this->property_id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zipCode' => $this->zip_code,
            'country' => $this->country,
            'propertyType' => $this->property_type,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'maxGuests' => $this->max_guests,
            'description' => $this->description,
            'amenities' => $this->amenities ?? [],
            'photos' => $this->images ?? [],
            'amenitiesList' => $this->amenities ?? [],
            'availability' => $this->availability ?? [
                'availableDates' => [],
                'blockedDates' => [],
                'lastUpdated' => $this->last_details_sync?->toISOString() ?? ''
            ],
            'detailsUrl' => $this->details_url,
            'managerFirstName' => $this->manager['firstName'] ?? $this->manager['first_name'] ?? '',
            'managerLastName' => $this->manager['lastName'] ?? $this->manager['last_name'] ?? '',
            'managerPhone' => $this->manager['phone'] ?? '',
            'businessName' => $this->manager['businessName'] ?? $this->manager['business_name'] ?? '',
            'emailAddressAccount' => $this->manager['email'] ?? '',
            'offLine' => $this->off_line,
            'lastUpdate' => $this->updated_at?->toISOString() ?? '',
            'accountId' => $this->account_id,
            'externalLinks' => $this->external_links ?? [],
            'bookingInfo' => $this->booking_info ?? []
        ];
    }

    /**
     * Verificar se precisa de sincronização
     */
    public function needsSummarySync(int $hours = 24): bool
    {
        if (!$this->last_summary_sync) {
            return true;
        }
        
        return $this->last_summary_sync->lt(now()->subHours($hours));
    }

    public function needsDetailsSync(int $hours = 24): bool
    {
        if (!$this->last_details_sync) {
            return true;
        }
        
        return $this->last_details_sync->lt(now()->subHours($hours));
    }

    /**
     * Marcar como sincronizado
     */
    public function markSummarySynced(): bool
    {
        return $this->update(['last_summary_sync' => now()]);
    }

    public function markDetailsSynced(): bool
    {
        return $this->update(['last_details_sync' => now()]);
    }

    /**
     * Validar dados da propriedade
     */
    public function validatePropertyData(): bool
    {
        $requiredFields = ['property_id', 'name'];
        
        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Converter camelCase para snake_case
     */
    public static function convertToSnakeCase(array $data): array
    {
        $converted = [];
        
        foreach ($data as $key => $value) {
            $snakeKey = match($key) {
                'propertyId' => 'property_id',
                'accountId' => 'account_id',
                'zipCode' => 'zip_code',
                'propertyType' => 'property_type',
                'maxGuests' => 'max_guests',
                'externalLinks' => 'external_links',
                'bookingInfo' => 'booking_info',
                'offLine' => 'off_line',
                'detailsUrl' => 'details_url',
                'rawSummaryData' => 'raw_summary_data',
                'rawDetailsData' => 'raw_details_data',
                'lastSummarySync' => 'last_summary_sync',
                'lastDetailsSync' => 'last_details_sync',
                default => $key
            };
            
            $converted[$snakeKey] = $value;
        }
        
        return $converted;
    }
}
