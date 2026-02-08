<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class ImportPropertyPhotos extends Command
{
    protected $signature = 'properties:import-photos {file=toplist.properties.json} {--dry-run : Não salva alterações, apenas simula}';
    protected $description = 'Importa fotos da coluna photos do JSON para as propriedades (por property_id)';

    public function handle()
    {
        $file = base_path($this->argument('file'));

        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return 1;
        }

        $this->info("Lendo arquivo: {$file}");

        // remove any PHP time limit for long imports
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $json = json_decode(file_get_contents($file), true);

        if (!is_array($json)) {
            $this->error('Arquivo JSON inválido');
            return 1;
        }

        // collect all property ids from the JSON first
        $allIds = [];
        foreach ($json as $item) {
            $propertyId = $item['property_id'] ?? $item['propertyId'] ?? ($item['summary']['property_id'] ?? null) ?? ($item['summary']['propertyId'] ?? null);
            if (!empty($propertyId)) {
                $allIds[] = (string) trim($propertyId);
            }
        }

        $allIds = array_values(array_unique($allIds));

        // Bulk fetch existing properties to reduce queries
        $propertiesById = Property::whereIn('property_id', $allIds)->get()->keyBy('property_id');

        $countUpdated = 0;
        $countFound = 0;
        $countNotFound = 0;

        $bar = $this->output->createProgressBar(count($json));
        $bar->start();

        $dryRun = (bool) $this->option('dry-run');
    
        foreach ($json as $item) {
            try {
                // property id may be in different keys depending on JSON structure
                $propertyId = $item['property_id'] ?? $item['propertyId'] ?? ($item['summary']['property_id'] ?? null) ?? ($item['summary']['propertyId'] ?? null);
                $propertyId = is_null($propertyId) ? null : (string) trim($propertyId);
                if (empty($propertyId)) {
                    $bar->advance();
                    continue;
                }

                // photos may be nested under details or summary
             
                $photos = $item['details']['photos'] ?? null;
                if (empty($photos) || !is_array($photos)) {
                    $bar->advance();
                    continue;
                }
            
                if (!$propertiesById->has($propertyId)) {
                    $countNotFound++;
                    $this->warn("Propriedade não encontrada: {$propertyId}");
                    $bar->advance();
                    continue;
                }

                $countFound++;
                $property = $propertiesById->get($propertyId);

                // current photos from DB (ensure array)
                $currentPhotos = $property->getOriginal('photos');
                
                if (is_null($currentPhotos)) {
                    $currentPhotos = [];
                } elseif (is_string($currentPhotos)) {
                    $decoded = json_decode($currentPhotos, true);
                    $currentPhotos = is_array($decoded) ? $decoded : [];
                } elseif (!is_array($currentPhotos)) {
                    $currentPhotos = [];
                }

                // Normalize incoming photos: keep absolute URLs as-is, trim whitespace
                $normalizedJsonPhotos = array_values(array_filter(array_map(function($p) {
                    if (is_string($p)) {
                        return trim($p);
                    }
                    return null;
                }, $photos)));

                // Compute photos that are new (not in current)
                $toAdd = array_values(array_diff($normalizedJsonPhotos, $currentPhotos));
                if (empty($toAdd)) {
                    $bar->advance();
                    continue;
                }

                $merged = array_values(array_unique(array_merge($currentPhotos, $toAdd)));
             
                if (!$dryRun) {
                    // save changes in a try/catch to avoid stopping the import on a single failure
                    try {
              
                        $property->photos = $merged;
                        $property->save();
          
                    } catch (\Exception $e) {
                        $this->error("Falha ao salvar propriedade {$propertyId}: " . $e->getMessage());
                        $bar->advance();
                        continue;
                    }
                }

                $countUpdated++;
                $this->info("\n" . ($dryRun ? '[DRY RUN] ' : '') . "Atualizada propriedade {$propertyId}: adicionadas " . count($toAdd) . " fotos (total agora: " . count($merged) . ")");
            } catch (\Exception $e) {
                $this->error("Erro processando item: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        $this->line('');
        $this->info("Importação finalizada. Propriedades encontradas no arquivo: " . count($allIds));
        $this->info("Propriedades correspondentes na base: {$countFound}");
        $this->info("Propriedades não encontradas: {$countNotFound}");
        $this->info("Propriedades atualizadas: {$countUpdated}");

        return 0;
    }
}
