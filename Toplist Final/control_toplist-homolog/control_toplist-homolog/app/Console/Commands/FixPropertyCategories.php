<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class FixPropertyCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'properties:fix-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige as categorias das propriedades baseado nos dados originais do JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔄 Corrigindo categorias das propriedades...");
        
        $properties = Property::where('source', 'bookerville')->get();
        
        if ($properties->isEmpty()) {
            $this->warn("❌ Nenhuma propriedade do Bookerville encontrada para corrigir");
            return Command::FAILURE;
        }

        $updated = 0;
        $errors = 0;
        
        $progressBar = $this->output->createProgressBar($properties->count());
        $progressBar->start();

        foreach ($properties as $property) {
            try {
                $originalData = $property->original_data;
                
                if (!$originalData || !isset($originalData['category'])) {
                    $errors++;
                    continue;
                }
                
                $originalCategory = $originalData['category'];
                
                // Só atualizar se a categoria for diferente
                if ($property->category !== $originalCategory) {
                    $property->update(['category' => $originalCategory]);
                    $updated++;
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("\n❌ Erro na propriedade {$property->property_id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Relatório final
        $this->info("✅ Correção concluída!");
        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Propriedades processadas', $properties->count()],
                ['Categorias corrigidas', $updated],
                ['Erros', $errors],
            ]
        );

        // Mostrar estatísticas atualizadas
        $resorts = Property::where('source', 'bookerville')->where('category', 'resort')->count();
        $propertiesCount = Property::where('source', 'bookerville')->where('category', 'property')->count();
        
        $this->newLine();
        $this->info("📊 Estatísticas atualizadas:");
        $this->table(
            ['Categoria', 'Quantidade'],
            [
                ['Resorts', $resorts],
                ['Properties', $propertiesCount],
                ['Total', $resorts + $propertiesCount]
            ]
        );

        return Command::SUCCESS;
    }
}
