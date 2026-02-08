<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar propriedades de exemplo usando o método createProperty
        $properties = [
            [
                'propertyId' => Str::uuid()->toString(),
                'title' => 'Apartamento moderno no centro',
                'summary' => [
                    'price' => 350000,
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'area' => 85,
                    'parking' => 1,
                    'description' => 'Apartamento moderno com vista para a cidade'
                ],
                'details' => [
                    'address' => [
                        'street' => 'Rua das Flores, 123',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'zip_code' => '01234-567'
                    ],
                    'features' => ['varanda', 'churrasqueira', 'piscina'],
                    'contact' => [
                        'agent_name' => 'João Silva',
                        'agent_phone' => '(11) 99999-9999'
                    ]
                ],
                'category' => 'Apartamento',
                'isActive' => true
            ],
            [
                'propertyId' => Str::uuid()->toString(),
                'title' => 'Casa com quintal amplo',
                'summary' => [
                    'price' => 450000,
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 120,
                    'parking' => 2,
                    'description' => 'Casa espaçosa ideal para famílias'
                ],
                'details' => [
                    'address' => [
                        'street' => 'Rua dos Jardins, 456',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'zip_code' => '02345-678'
                    ],
                    'features' => ['quintal', 'garagem', 'área gourmet'],
                    'contact' => [
                        'agent_name' => 'Maria Santos',
                        'agent_phone' => '(11) 88888-8888'
                    ]
                ],
                'category' => 'Casa',
                'isActive' => true
            ],
            [
                'propertyId' => Str::uuid()->toString(),
                'title' => 'Studio compacto para investidor',
                'summary' => [
                    'price' => 180000,
                    'bedrooms' => 1,
                    'bathrooms' => 1,
                    'area' => 35,
                    'parking' => 0,
                    'description' => 'Studio ideal para investimento ou jovens profissionais'
                ],
                'details' => [
                    'address' => [
                        'street' => 'Av. Paulista, 789',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'zip_code' => '03456-789'
                    ],
                    'features' => ['mobiliado', 'ar condicionado'],
                    'contact' => [
                        'agent_name' => 'Carlos Oliveira',
                        'agent_phone' => '(11) 77777-7777'
                    ]
                ],
                'category' => 'Studio',
                'isActive' => true
            ]
        ];

        foreach ($properties as $propertyData) {
            Property::createProperty($propertyData);
        }

        // Criar mais propriedades usando o Factory
        Property::factory(10)->create();

        $this->command->info('Propriedades criadas com sucesso!');
        $this->command->info('Total: ' . Property::count() . ' propriedades');
    }
}
