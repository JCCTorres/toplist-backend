<?php

namespace Database\Seeders;

use App\Services\ClientPropertyService;
use Illuminate\Database\Seeder;

class ClientPropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientPropertyService = new ClientPropertyService();

        // Dados de exemplo simulando importação de JSON
        $properties = [
            [
                'airbnbId' => 12345678,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/12345678',
                'title' => 'Beautiful Apartment in Downtown',
                'houseNumber' => '123',
                'owner' => 'João Silva',
                'observations' => 'Property with great view',
                'address' => 'Rua das Flores, 123, Centro, São Paulo, SP'
            ],
            [
                'airbnbId' => 87654321,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/87654321',
                'title' => 'Cozy House Near Beach',
                'houseNumber' => '456',
                'owner' => 'Maria Santos',
                'observations' => 'Walking distance to beach',
                'address' => 'Av. Beira Mar, 456, Copacabana, Rio de Janeiro, RJ'
            ],
            [
                'airbnbId' => 11223344,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/11223344',
                'title' => 'Modern Studio in Business District',
                'houseNumber' => null,
                'owner' => 'Carlos Oliveira',
                'observations' => 'Perfect for business travelers',
                'address' => 'Av. Paulista, 789, Bela Vista, São Paulo, SP'
            ],
            [
                'airbnbId' => 55667788,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/55667788',
                'title' => 'Family House with Pool',
                'houseNumber' => '789',
                'owner' => 'Ana Costa',
                'observations' => 'Great for families, has pool and garden',
                'address' => 'Rua dos Jardins, 789, Jardins, São Paulo, SP'
            ],
            [
                'airbnbId' => 99887766,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/99887766',
                'title' => 'Penthouse with City View',
                'houseNumber' => 'PH-01',
                'owner' => 'Roberto Lima',
                'observations' => 'Luxury penthouse with amazing city view',
                'address' => 'Av. Atlântica, 1000, Copacabana, Rio de Janeiro, RJ'
            ]
        ];

        foreach ($properties as $propertyData) {
            $clientPropertyService->create($propertyData);
        }

        // Criar algumas propriedades que precisam de sincronização
        $needingSyncProperties = [
            [
                'airbnbId' => 11111111,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/11111111',
                'title' => 'Property Needing Sync 1',
                'houseNumber' => '001',
                'owner' => 'Test Owner 1',
                'observations' => 'This property needs synchronization',
                'address' => 'Test Address 1'
            ],
            [
                'airbnbId' => 22222222,
                'airbnbUrl' => 'https://www.airbnb.com/rooms/22222222',
                'title' => 'Property Needing Sync 2',
                'houseNumber' => '002',
                'owner' => 'Test Owner 2',
                'observations' => 'This property also needs synchronization',
                'address' => 'Test Address 2'
            ]
        ];

        foreach ($needingSyncProperties as $propertyData) {
            $property = $clientPropertyService->create($propertyData);
            // Simular que essas propriedades têm last_sync antigo (mais de 1 hora)
            \App\Models\ClientProperty::where('airbnb_id', $property->airbnb_id)
                ->update(['last_sync' => now()->subHours(2)]);
        }

        $this->command->info('Client Properties criadas com sucesso!');
        $this->command->info('Total: 7 propriedades (5 normais + 2 precisando de sync)');
        
        // Mostrar estatísticas
        $stats = $clientPropertyService->getStats();
        $this->command->info("Estatísticas:");
        $this->command->info("- Total: {$stats['total']}");
        $this->command->info("- Precisam de sync: {$stats['needing_sync']}");
        $this->command->info("- Sincronizadas recentemente: {$stats['recently_sync']}");
    }
}
