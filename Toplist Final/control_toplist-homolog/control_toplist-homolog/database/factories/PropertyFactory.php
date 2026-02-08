<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Apartamento', 'Casa', 'Sobrado', 'Cobertura', 'Studio', 'Loft', 'Comercial'];
        
        return [
            'property_id' => fake()->unique()->uuid(),
            'title' => fake()->sentence(4),
            'summary' => [
                'price' => fake()->numberBetween(100000, 2000000),
                'bedrooms' => fake()->numberBetween(1, 5),
                'bathrooms' => fake()->numberBetween(1, 4),
                'area' => fake()->numberBetween(30, 500),
                'parking' => fake()->numberBetween(0, 3),
                'description' => fake()->text(200),
            ],
            'details' => [
                'address' => [
                    'street' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->stateAbbr(),
                    'zip_code' => fake()->postcode(),
                    'neighborhood' => fake()->citySuffix(),
                ],
                'features' => fake()->words(5),
                'amenities' => fake()->words(8),
                'images' => [
                    fake()->imageUrl(800, 600, 'house', true, 'property'),
                    fake()->imageUrl(800, 600, 'house', true, 'room'),
                    fake()->imageUrl(800, 600, 'house', true, 'kitchen'),
                ],
                'contact' => [
                    'agent_name' => fake()->name(),
                    'agent_phone' => fake()->phoneNumber(),
                    'agent_email' => fake()->email(),
                ],
            ],
            'category' => fake()->randomElement($categories),
            'is_active' => fake()->boolean(85), // 85% chance de estar ativo
            'last_sync' => fake()->dateTimeBetween('-30 days', 'now'),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the property is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the property is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific category for the property.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
