<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClientProperty;
use Carbon\Carbon;

class ImportClientProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client-properties:import {file : Caminho para o arquivo JSON das client properties}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa client properties do arquivo JSON para o banco de dados';

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

        $clientProperties = json_decode($jsonContent, true);
        if (!$clientProperties) {
            $this->error("❌ Erro ao decodificar JSON: " . json_last_error_msg());
            return Command::FAILURE;
        }

        $this->info("🔄 Importando " . count($clientProperties) . " client properties...");
        
        $imported = 0;
        $updated = 0;
        $errors = 0;
        
        $progressBar = $this->output->createProgressBar(count($clientProperties));
        $progressBar->start();

        foreach ($clientProperties as $index => $clientPropertyData) {
            try {
                $result = $this->importClientProperty($clientPropertyData);
                
                if ($result['action'] === 'created') {
                    $imported++;
                } elseif ($result['action'] === 'updated') {
                    $updated++;
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Erro na client property " . ($clientPropertyData['airbnbId'] ?? 'unknown') . ": " . $e->getMessage());
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
                ['Total processadas', count($clientProperties)]
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Importa uma client property individual
     */
    private function importClientProperty(array $clientPropertyData): array
    {
        $airbnbId = $clientPropertyData['airbnbId'] ?? null;
        
        if (!$airbnbId) {
            throw new \Exception('Airbnb ID não encontrado');
        }

        // Verificar se já existe
        $existingClientProperty = ClientProperty::where('airbnb_id', $airbnbId)->first();

        // Preparar dados para salvar
        $data = $this->prepareClientPropertyData($clientPropertyData);

        if ($existingClientProperty) {
            $existingClientProperty->update($data);
            return ['action' => 'updated', 'client_property' => $existingClientProperty];
        } else {
            $newClientProperty = ClientProperty::create($data);
            return ['action' => 'created', 'client_property' => $newClientProperty];
        }
    }

    /**
     * Prepara os dados da client property para inserção no banco
     */
    private function prepareClientPropertyData(array $clientPropertyData): array
    {
        return [
            'airbnb_id' => $clientPropertyData['airbnbId'],
            'airbnb_url' => $clientPropertyData['airbnbUrl'] ?? '',
            'title' => $clientPropertyData['title'] ?? '',
            'house_number' => $clientPropertyData['houseNumber'] ?? null,
            'owner' => $clientPropertyData['owner'] ?? null,
            'observations' => $clientPropertyData['observations'] ?? null,
            'address' => $clientPropertyData['address'] ?? null,
            'last_sync' => Carbon::now(),
            // Salvar dados originais para referência
            'bookerville_data' => $clientPropertyData,
            // Preservar timestamps originais se existirem
            'created_at' => $this->parseDate($clientPropertyData['createdAt']['$date'] ?? null) ?? Carbon::now(),
            'updated_at' => $this->parseDate($clientPropertyData['updatedAt']['$date'] ?? null) ?? Carbon::now(),
        ];
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
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }
}
