<?php

namespace App\Services;

use App\Models\ClientProperty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientPropertyService
{
    /**
     * Criar nova propriedade do cliente (equivalente ao create do MongoDB)
     */
    public function create(array $data): ClientProperty
    {
        // Remove campos que serão definidos automaticamente
        unset($data['_id'], $data['created_at'], $data['updated_at']);
        
        // Converte camelCase para snake_case se necessário
        $data = ClientProperty::convertToSnakeCase($data);
        
        return ClientProperty::create($data);
    }

    /**
     * Buscar por airbnb_id (equivalente ao findByAirbnbId do MongoDB)
     */
    public function findByAirbnbId(int $airbnbId): ?ClientProperty
    {
        return ClientProperty::where('airbnb_id', $airbnbId)->first();
    }

    /**
     * Buscar todas as propriedades (equivalente ao findAll do MongoDB)
     */
    public function findAll(): Collection
    {
        return ClientProperty::all();
    }

    /**
     * Atualizar propriedade por airbnb_id (equivalente ao update do MongoDB)
     */
    public function update(int $airbnbId, array $updates): bool
    {
        // Remove campos que não devem ser atualizados manualmente
        unset($updates['_id'], $updates['created_at']);
        
        // Converte camelCase para snake_case se necessário
        $updates = ClientProperty::convertToSnakeCase($updates);
        
        // Adiciona updated_at automaticamente
        $updates['updated_at'] = now();
        
        return ClientProperty::where('airbnb_id', $airbnbId)->update($updates) > 0;
    }

    /**
     * Deletar propriedade por airbnb_id (equivalente ao delete do MongoDB)
     */
    public function delete(int $airbnbId): bool
    {
        return ClientProperty::where('airbnb_id', $airbnbId)->delete() > 0;
    }

    /**
     * Buscar propriedades que precisam de sincronização (equivalente ao findNeedingSync do MongoDB)
     */
    public function findNeedingSync(): Collection
    {
        $oneHourAgo = now()->subHour();
        
        return ClientProperty::where(function ($query) use ($oneHourAgo) {
            $query->whereNull('last_sync')
                  ->orWhere('last_sync', '<', $oneHourAgo);
        })->get();
    }

    /**
     * Atualizar dados do Bookerville (equivalente ao updateBookervilleData do MongoDB)
     */
    public function updateBookervilleData(int $airbnbId, array $bookervilleData): bool
    {
        return ClientProperty::where('airbnb_id', $airbnbId)->update([
            'bookerville_data' => $bookervilleData,
            'last_sync' => now(),
            'updated_at' => now()
        ]) > 0;
    }

    /**
     * Importar propriedades do JSON (equivalente ao importFromJson do MongoDB)
     */
    public function importFromJson(array $properties): array
    {
        $created = 0;
        $updated = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($properties as $prop) {
                try {
                    // Mapear campos do JSON para o modelo
                    $propertyData = $this->mapJsonToModel($prop);

                    // Verificar se já existe
                    $existing = $this->findByAirbnbId($propertyData['airbnb_id']);

                    if ($existing) {
                        // Atualizar se existir
                        $this->update($propertyData['airbnb_id'], $propertyData);
                        $updated++;
                    } else {
                        // Criar se não existir
                        $this->create($propertyData);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errorId = $prop['Listing ID (JSON file e Folder name)'] ?? $prop['ID'] ?? 'unknown';
                    Log::error("Erro ao importar propriedade {$errorId}: " . $e->getMessage());
                    $errors++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Erro geral na importação: " . $e->getMessage());
            throw $e;
        }

        return compact('created', 'updated', 'errors');
    }

    /**
     * Mapear dados do JSON para o modelo
     */
    private function mapJsonToModel(array $prop): array
    {
        // Tratar o ID do Airbnb
        $airbnbId = $prop['Listing ID (JSON file e Folder name)'] ?? $prop['ID'] ?? null;
        
        if (!$airbnbId) {
            throw new \InvalidArgumentException('ID do Airbnb não encontrado');
        }

        // Converter para inteiro se for string
        $airbnbId = is_numeric($airbnbId) ? (int) $airbnbId : null;
        
        if (!$airbnbId) {
            throw new \InvalidArgumentException('ID do Airbnb inválido');
        }

        return [
            'airbnb_id' => $airbnbId,
            'airbnb_url' => $prop['URL'] ?? '',
            'title' => $prop['Title'] ?? '',
            'house_number' => ($prop['numero da casa'] ?? null) === '#N/A' ? null : ($prop['numero da casa'] ?? null),
            'owner' => ($prop['dono'] ?? null) === '#N/A' ? null : ($prop['dono'] ?? null),
            'observations' => $prop['obs'] ?? null,
            'address' => $prop['Address'] ?? null,
        ];
    }

    /**
     * Buscar propriedades por proprietário
     */
    public function findByOwner(string $owner): Collection
    {
        return ClientProperty::byOwner($owner)->get();
    }

    /**
     * Buscar propriedades sincronizadas recentemente
     */
    public function findRecentlySync(int $hours = 24): Collection
    {
        return ClientProperty::recentlySync($hours)->get();
    }

    /**
     * Marcar propriedade como sincronizada
     */
    public function markAsSynced(int $airbnbId): bool
    {
        return ClientProperty::where('airbnb_id', $airbnbId)->update([
            'last_sync' => now()
        ]) > 0;
    }

    /**
     * Obter estatísticas das propriedades
     */
    public function getStats(): array
    {
        $total = ClientProperty::count();
        $needingSync = $this->findNeedingSync()->count();
        $recentlySync = $this->findRecentlySync()->count();
        $withBookervilleData = ClientProperty::whereNotNull('bookerville_data')->count();

        return [
            'total' => $total,
            'needing_sync' => $needingSync,
            'recently_sync' => $recentlySync,
            'with_bookerville_data' => $withBookervilleData,
            'sync_percentage' => $total > 0 ? round(($recentlySync / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Buscar propriedades paginadas
     */
    public function findPaginated(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = ClientProperty::query();

        // Aplicar filtros
        if (!empty($filters['owner'])) {
            $query->byOwner($filters['owner']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('observations', 'like', "%{$search}%");
            });
        }

        if (isset($filters['needs_sync']) && $filters['needs_sync']) {
            $oneHourAgo = now()->subHour();
            $query->where(function ($q) use ($oneHourAgo) {
                $q->whereNull('last_sync')
                  ->orWhere('last_sync', '<', $oneHourAgo);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}