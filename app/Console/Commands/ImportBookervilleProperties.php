<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use Carbon\Carbon;

class ImportBookervilleProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookerville:import {file : Caminho para o arquivo JSON das propriedades do Bookerville}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa propriedades do arquivo JSON do Bookerville para o banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("📁 Lendo arquivo: {$filePath}");
        
        $jsonContent = file_get_contents($filePath);
        if (!$jsonContent) {
            $this->error("❌ Erro ao ler o arquivo");
            return Command::FAILURE;
        }

        $properties = json_decode($jsonContent, true);
        if (!$properties) {
            $this->error("❌ Erro ao decodificar JSON: " . json_last_error_msg());
            return Command::FAILURE;
        }

        $this->info("🔄 Importando " . count($properties) . " propriedades...");
        
        $imported = 0;
        $updated = 0;
        $errors = 0;
        
        $progressBar = $this->output->createProgressBar(count($properties));
        $progressBar->start();

        foreach ($properties as $index => $propertyData) {
            try {
                $result = $this->importProperty($propertyData);
                
                if ($result['action'] === 'created') {
                    $imported++;
                } elseif ($result['action'] === 'updated') {
                    $updated++;
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Erro na propriedade " . ($propertyData['propertyId'] ?? 'unknown') . ": " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Relatório final
        $this->info("✅ Importação concluída!");
        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Importadas', $imported],
                ['Atualizadas', $updated],
                ['Erros', $errors],
                ['Total processadas', count($properties)]
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Importa uma propriedade individual
     */
    private function importProperty(array $propertyData): array
    {
        $propertyId = $propertyData['propertyId'] ?? null;
        
        if (!$propertyId) {
            throw new \Exception('Property ID não encontrado');
        }

        // Verificar se já existe
        $existingProperty = Property::where('property_id', $propertyId)->first();

        // Preparar dados para salvar
        $data = $this->preparePropertyData($propertyData);

        if ($existingProperty) {
            $existingProperty->update($data);
            return ['action' => 'updated', 'property' => $existingProperty];
        } else {
            $newProperty = Property::create($data);
            return ['action' => 'created', 'property' => $newProperty];
        }
    }

    /**
     * Prepara os dados da propriedade para inserção no banco
     */
    private function preparePropertyData(array $propertyData): array
    {
        $details = $propertyData['details'] ?? [];
        $summary = $propertyData['summary'] ?? [];
        
        return [
            'property_id' => $propertyData['propertyId'],
            'title' => $propertyData['title'] ?? $details['name'] ?? '',
            'address' => $details['address'] ?? '',
            'zip_code' => $details['zipCode'] ?? '',
            'country' => $details['country'] ?? '',
            'property_type' => $details['propertyType'] ?? '',
            'max_guests' => $details['maxGuests'] ?? 0,
            'description' => $details['description'] ?? '',
            'main_image' => $details['mainImage'] ?? '',
            'amenities' => $details['amenities'] ?? [],
            'photos' => $details['photos'] ?? [],
            'summary_data' => $summary,
            'bookerville_id' => $propertyData['propertyId'],
            'bkv_account_id' => $summary['bkvAccountId'] ?? '',
            'manager_first_name' => $summary['managerFirstName'] ?? '',
            'manager_last_name' => $summary['managerLastName'] ?? '',
            'manager_phone' => $summary['managerPhone'] ?? '',
            'business_name' => $summary['businessName'] ?? '',
            'email_address_account' => $summary['emailAddressAccount'] ?? '',
            'off_line' => $summary['offLine'] ?? false,
            'property_details_api_url' => $summary['property_details_api_url'] ?? '',
            'bookerville_last_update' => $this->parseDate($summary['last_update'] ?? null),
            'bookerville_created_at' => $this->parseDate($propertyData['createdAt']['$date'] ?? null),
            'bookerville_updated_at' => $this->parseDate($propertyData['updatedAt']['$date'] ?? null),
            'category' => $propertyData['category'] ?? 'property', // Usar categoria do JSON
            'source' => 'bookerville',
            'original_data' => $propertyData,
            'is_active' => $propertyData['isActive'] ?? true,
            'last_sync' => Carbon::now(),
            // Campos de compatibilidade com estrutura antiga
            'summary' => $summary,
            'details' => $details,
        ];
    }

    /**
     * Determina a categoria da propriedade (resort ou property)
     */
    private function determineCategory(array $details): string
    {
        $resortKeywords = [
            'resort', 'hotel', 'complex', 'lodge', 'inn', 'spa',
            'club', 'village', 'community', 'estate', 'fantasy', 'world'
        ];

        $name = strtolower($details['name'] ?? '');
        $propertyType = strtolower($details['propertyType'] ?? '');
        $description = strtolower($details['description'] ?? '');

        // Verificar se contém palavras-chave de resort
        foreach ($resortKeywords as $keyword) {
            if (str_contains($name, $keyword) || 
                str_contains($propertyType, $keyword) || 
                str_contains($description, $keyword)) {
                return 'resort';
            }
        }

        // Verificar se tem muitas amenidades (indicativo de resort)
        $amenities = $details['amenities'] ?? [];
        if (is_array($amenities) && count($amenities) >= 15) {
            return 'resort';
        }

        // Verificar se tem muitos quartos (indicativo de resort)
        $bedrooms = $details['bedrooms'] ?? 0;
        if ($bedrooms >= 4) {
            return 'resort';
        }

        return 'property';
    }

    /**
     * Converte datas do MongoDB para formato Carbon
     */
    private function parseDate($dateString): ?Carbon
    {
        if (!$dateString) {
            return null;
        }

        try {
            // Se é uma data do MongoDB com $date
            if (is_array($dateString) && isset($dateString['$date'])) {
                return Carbon::parse($dateString['$date']);
            }
            
            // Se é uma string de data normal
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }
}
