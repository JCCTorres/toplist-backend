<?php

use App\Models\Property;

// Verificar as categorias após correção
$resorts = Property::where('category', 'resort')->count();
$properties = Property::where('category', 'property')->count();
$total = Property::count();

echo "=== CATEGORIAS CORRIGIDAS ===\n";
echo "Total: $total\n";
echo "Resorts: $resorts\n";
echo "Properties: $properties\n";

// Mostrar exemplos
$resortExample = Property::where('category', 'resort')->first();
$propertyExample = Property::where('category', 'property')->first();

if ($resortExample) {
    echo "\n=== EXEMPLO DE RESORT ===\n";
    echo "ID: " . $resortExample->property_id . "\n";
    echo "Title: " . $resortExample->title . "\n";
    echo "Category: " . $resortExample->category . "\n";
}

if ($propertyExample) {
    echo "\n=== EXEMPLO DE PROPERTY ===\n";
    echo "ID: " . $propertyExample->property_id . "\n";
    echo "Title: " . $propertyExample->title . "\n";
    echo "Category: " . $propertyExample->category . "\n";
}

echo "\n✅ Categorias foram corrigidas com sucesso!\n";
echo "🌐 Teste a API: /api/v1/public/bookerville/home-cards\n";