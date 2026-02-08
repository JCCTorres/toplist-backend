<?php

use App\Models\Property;

// Verificar se há propriedades para testar
$total = Property::count();
$resorts = Property::where('category', 'resort')->count();
$properties = Property::where('category', 'property')->count();

echo "=== VERIFICAÇÃO DE DADOS ===\n";
echo "Total de propriedades: $total\n";
echo "Resorts: $resorts\n";
echo "Properties: $properties\n";

// Testar uma propriedade
$sampleProperty = Property::where('category', 'resort')->first();
if ($sampleProperty) {
    echo "\n=== AMOSTRA DE RESORT ===\n";
    echo "ID: " . $sampleProperty->property_id . "\n";
    echo "Title: " . $sampleProperty->title . "\n";
    echo "Main Image: " . $sampleProperty->main_image . "\n";
    echo "Category: " . $sampleProperty->category . "\n";
}

echo "\nAPI disponível em: /api/v1/public/bookerville/home-cards\n";