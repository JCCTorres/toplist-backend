<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use Carbon\Carbon;

class ImportBookervilleProperties extends Command
{
    /**
     * Bookerville property_id => Airbnb listing ID mapping
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
        $propertyId = $propertyData['propertyId'];

        // Look up Airbnb ID from mapping
        $airbnbId = self::AIRBNB_ID_MAPPING[$propertyId] ?? null;

        return [
            'property_id' => $propertyId,
            'airbnb_id' => $airbnbId,
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
            'bookerville_id' => $propertyId,
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
