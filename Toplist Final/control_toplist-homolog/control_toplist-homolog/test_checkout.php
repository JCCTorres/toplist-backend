<?php

use App\Models\ClientProperty;

// Verificar se há client_properties para testar
$totalClientProperties = ClientProperty::count();
$sampleProperty = ClientProperty::first();

echo "=== VERIFICAÇÃO CLIENT PROPERTIES ===\n";
echo "Total de client_properties: $totalClientProperties\n";

if ($sampleProperty) {
    echo "\n=== AMOSTRA DE CLIENT PROPERTY ===\n";
    echo "ID: " . $sampleProperty->airbnb_id . "\n";
    echo "Title: " . $sampleProperty->title . "\n";
    echo "Address: " . $sampleProperty->address . "\n";
    echo "Airbnb URL: " . $sampleProperty->airbnb_url . "\n";
    echo "Owner: " . $sampleProperty->owner . "\n";
}

echo "\n🌐 APIs disponíveis:\n";
echo "- Home Cards: /api/bookerville/home-cards\n";
echo "- All Properties: /api/bookerville/all-properties\n";
echo "- Airbnb Checkout: POST /api/bookerville/properties/{airbnbId}/airbnb-checkout\n";
echo "\n📋 Exemplo de payload para checkout:\n";
echo "{\n";
echo "  \"checkin\": \"2025-10-01\",\n";
echo "  \"checkout\": \"2025-10-03\",\n";
echo "  \"numberOfGuests\": 4,\n";
echo "  \"numberOfAdults\": 2,\n";
echo "  \"numberOfChildren\": 2,\n";
echo "  \"address\": \"opcional - endereço específico\"\n";
echo "}\n";