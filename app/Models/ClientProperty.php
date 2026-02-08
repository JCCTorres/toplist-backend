<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProperty extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'client_properties';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'airbnb_id',
        'airbnb_url',
        'title',
        'house_number',
        'owner',
        'observations',
        'address',
        'bookerville_data',
        'last_sync',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'airbnb_id' => 'integer',
        'bookerville_data' => 'array',
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
        return 'airbnb_id';
    }

    /**
     * Scope para buscar por proprietário
     */
    public function scopeByOwner($query, $owner)
    {
        return $query->where('owner', $owner);
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
     * Converter para formato compatível com a interface TypeScript
     */
    public function toTypeScriptFormat(): array
    {
        return [
            '_id' => (string) $this->id,
            'airbnbId' => $this->airbnb_id,
            'airbnbUrl' => $this->airbnb_url,
            'title' => $this->title,
            'houseNumber' => $this->house_number,
            'owner' => $this->owner,
            'observations' => $this->observations,
            'address' => $this->address,
            'bookervilleData' => $this->bookerville_data,
            'lastSync' => $this->last_sync,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
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

    /**
     * Converter camelCase para snake_case
     */
    public static function convertToSnakeCase(array $data): array
    {
        $converted = [];
        
        foreach ($data as $key => $value) {
            $snakeKey = match($key) {
                'airbnbId' => 'airbnb_id',
                'airbnbUrl' => 'airbnb_url',
                'houseNumber' => 'house_number',
                'bookervilleData' => 'bookerville_data',
                'lastSync' => 'last_sync',
                'createdAt' => 'created_at',
                'updatedAt' => 'updated_at',
                default => $key
            };
            
            $converted[$snakeKey] = $value;
        }
        
        return $converted;
    }
}
