<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'properties';

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Evento antes de atualizar o modelo
        static::updating(function ($property) {
            $property->handleImageDeletion();
        });

        // Evento antes de deletar o modelo
        static::deleting(function ($property) {
            $property->deleteAllImages();
        });
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'property_id',
        'airbnb_id',
        'airbnb_url',
        'title',
        'house_number',
        'owner',
        'observations',
        'address',
        'zip_code',
        'country',
        'property_type',
        'max_guests',
        'description',
        'main_image',
        'main_image_name',
        'amenities',
        'photos',
        'summary_data',
        'bookerville_id',
        'bkv_account_id',
        'manager_first_name',
        'manager_last_name',
        'manager_phone',
        'business_name',
        'email_address_account',
        'off_line',
        'property_details_api_url',
        'bookerville_last_update',
        'bookerville_created_at',
        'bookerville_updated_at',
        'summary',
        'details',
        'category',
        'source',
        'original_data',
        'is_active',
        'last_sync',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'airbnb_id' => 'string',
        'max_guests' => 'integer',
        'amenities' => 'array',
        'photos' => 'array',
        'summary_data' => 'array',
        'off_line' => 'boolean',
        'bookerville_last_update' => 'datetime',
        'bookerville_created_at' => 'datetime',
        'bookerville_updated_at' => 'datetime',
        'summary' => 'array',
        'details' => 'array',
        'original_data' => 'array',
        'is_active' => 'boolean',
        'last_sync' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'property_id';
    }

    /**
     * Scope para buscar apenas propriedades ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por categoria
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para buscar propriedades sincronizadas recentemente
     */
    public function scopeRecentlySync($query, $hours = 24)
    {
        return $query->where('last_sync', '>=', now()->subHours($hours));
    }

    /**
     * Accessor para obter o ID no formato _id (compatibilidade com MongoDB)
     */
    public function get_IdAttribute()
    {
        return $this->id;
    }

    /**
     * Mutator para definir is_active como true por padrão
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = $value ?? true;
    }

    /**
     * Accessor para main_image - retorna URL completa ou caminho do storage
     */
    public function getMainImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // Se já é uma URL completa (https/http), retorna como está
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Para o Filament, retorna apenas o caminho relativo
        return $value;
    }

    /**
     * Accessor para obter URL completa da main_image
     */
    public function getMainImageUrlAttribute()
    {
        if (empty($this->attributes['main_image'])) {
            return null;
        }

        $value = $this->attributes['main_image'];

        // Se já é uma URL completa (https/http), retorna como está
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Usar asset() que é mais confiável
        return asset('storage/' . $value);
    }

    /**
     * Accessor para photos - retorna array de URLs completas ou caminhos do storage
     */
    // public function getPhotosAttribute($value)
    // {
    //     if (empty($value)) {
    //         return [];
    //     }

    //     // Se já é um array, processa cada item
    //     if (is_array($value)) {
    //         return array_map(function($photo) {
    //             if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
    //                 return $photo;
    //             }
    //             return asset('storage/' . $photo);
    //         }, $value);
    //     }

    //     // Se é string JSON, decodifica primeiro
    //     if (is_string($value)) {
    //         $photos = json_decode($value, true);
    //         if (is_array($photos)) {
    //             return array_map(function($photo) {
    //                 if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
    //                     return $photo;
    //                 }
    //                 return asset('storage/' . $photo);
    //             }, $photos);
    //         }
    //     }

    //     return [];
    // }

    // ========================================
    // MÉTODOS EQUIVALENTES AO MODEL MONGODB
    // ========================================

    /**
     * Criar nova propriedade (equivalente ao create do MongoDB)
     */
    public static function createProperty(array $data): self
    {
        // Remove campos que serão definidos automaticamente
        unset($data['_id'], $data['created_at'], $data['updated_at']);
        
        // Converte camelCase para snake_case se necessário
        $data = self::convertToSnakeCase($data);
        
        return self::create($data);
    }

    /**
     * Buscar por property_id (equivalente ao findByPropertyId do MongoDB)
     */
    public static function findByPropertyId(string $propertyId): ?self
    {
        return self::where('property_id', $propertyId)->first();
    }

    /**
     * Buscar todas as propriedades (equivalente ao findAll do MongoDB)
     */
    public static function findAll(): \Illuminate\Database\Eloquent\Collection
    {
        return self::all();
    }

    /**
     * Atualizar propriedade por property_id (equivalente ao update do MongoDB)
     */
    public static function updateByPropertyId(string $propertyId, array $updates): bool
    {
        // Remove campos que não devem ser atualizados manualmente
        unset($updates['_id'], $updates['created_at']);
        
        // Converte camelCase para snake_case se necessário
        $updates = self::convertToSnakeCase($updates);
        
        // Adiciona updated_at automaticamente
        $updates['updated_at'] = now();
        
        return self::where('property_id', $propertyId)->update($updates) > 0;
    }

    /**
     * Deletar propriedade por property_id (equivalente ao delete do MongoDB)
     */
    public static function deleteByPropertyId(string $propertyId): bool
    {
        return self::where('property_id', $propertyId)->delete() > 0;
    }

    /**
     * Buscar propriedades que precisam de sincronização (equivalente ao findNeedingSync do MongoDB)
     */
    public static function findNeedingSync(): \Illuminate\Database\Eloquent\Collection
    {
        $oneHourAgo = now()->subHour();
        
        return self::where(function ($query) use ($oneHourAgo) {
            $query->whereNull('last_sync')
                  ->orWhere('last_sync', '<', $oneHourAgo);
        })->get();
    }

    /**
     * Atualizar dados do summary (equivalente ao updateSummary do MongoDB)
     */
    public static function updateSummaryByPropertyId(string $propertyId, array $summary): bool
    {
        return self::where('property_id', $propertyId)->update([
            'summary' => $summary,
            'last_sync' => now(),
            'updated_at' => now()
        ]) > 0;
    }

    /**
     * Atualizar dados do details (equivalente ao updateDetails do MongoDB)
     */
    public static function updateDetailsByPropertyId(string $propertyId, array $details): bool
    {
        return self::where('property_id', $propertyId)->update([
            'details' => $details,
            'last_sync' => now(),
            'updated_at' => now()
        ]) > 0;
    }

    // ========================================
    // MÉTODOS DE GERENCIAMENTO DE IMAGENS
    // ========================================

    /**
     * Handle image deletion when updating model
     */
    protected function handleImageDeletion()
    {
        // Verifica se main_image foi alterada
        if ($this->isDirty('main_image')) {
            $oldMainImage = $this->getOriginal('main_image');
            if ($oldMainImage && !str_starts_with($oldMainImage, 'http')) {
                $this->deleteImageFromStorage($oldMainImage);
            }
        }

        // Verifica se photos foram alteradas
        if ($this->isDirty('photos')) {
            $oldPhotos = $this->getOriginal('photos');
            $newPhotos = $this->photos;

            if (is_array($oldPhotos)) {
                foreach ($oldPhotos as $oldPhoto) {
                    // Se a foto antiga não está na nova lista, deleta
                    if (!str_starts_with($oldPhoto, 'http') && 
                        (!is_array($newPhotos) || !in_array($oldPhoto, $newPhotos))) {
                        $this->deleteImageFromStorage($oldPhoto);
                    }
                }
            }
        }
    }

    /**
     * Delete all images when model is being deleted
     */
    protected function deleteAllImages()
    {
        // Deleta main_image
        if ($this->main_image && !str_starts_with($this->main_image, 'http')) {
            $this->deleteImageFromStorage($this->main_image);
        }

        // Deleta photos
        if (is_array($this->photos)) {
            foreach ($this->photos as $photo) {
                if (!str_starts_with($photo, 'http')) {
                    $this->deleteImageFromStorage($photo);
                }
            }
        }
    }

    /**
     * Delete specific image from storage
     */
    protected function deleteImageFromStorage(string $imagePath)
    {
        try {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info("Deleted image: {$imagePath}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete image {$imagePath}: " . $e->getMessage());
        }
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Converter camelCase para snake_case
     */
    private static function convertToSnakeCase(array $data): array
    {
        $converted = [];
        
        foreach ($data as $key => $value) {
            $snakeKey = match($key) {
                'propertyId' => 'property_id',
                'isActive' => 'is_active',
                'createdAt' => 'created_at',
                'updatedAt' => 'updated_at',
                'lastSync' => 'last_sync',
                default => $key
            };
            
            $converted[$snakeKey] = $value;
        }
        
        return $converted;
    }

    /**
     * Converter para formato compatível com a interface TypeScript
     */
    public function toTypeScriptFormat(): array
    {
        return [
            '_id' => (string) $this->id,
            'propertyId' => $this->property_id,
            'title' => $this->title,
            'summary' => $this->summary,
            'details' => $this->details,
            'category' => $this->category,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'lastSync' => $this->last_sync,
        ];
    }

    /**
     * Sincronizar propriedade (marca como sincronizada)
     */
    public function markAsSynced(): bool
    {
        return $this->update(['last_sync' => now()]);
    }

    /**
     * Verificar se precisa de sincronização
     */
    public function needsSync(): bool
    {
        if (!$this->last_sync) {
            return true;
        }
        
        return $this->last_sync->lt(now()->subHour());
    }
}
