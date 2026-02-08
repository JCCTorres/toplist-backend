<?php

use App\Models\Property;

// Buscar uma propriedade do Bookerville
$bookervilleProperty = Property::where('source', 'bookerville')->first();

if ($bookervilleProperty) {
    echo "=== PROPRIEDADE BOOKERVILLE ENCONTRADA ===\n";
    echo "ID: " . $bookervilleProperty->property_id . "\n";
    echo "Title: " . $bookervilleProperty->title . "\n";
    echo "Address: " . $bookervilleProperty->address . "\n";
    echo "Property Type: " . $bookervilleProperty->property_type . "\n";
    echo "Max Guests: " . $bookervilleProperty->max_guests . "\n";
    echo "Main Image: " . $bookervilleProperty->main_image . "\n";
    echo "Manager: " . $bookervilleProperty->manager_first_name . " " . $bookervilleProperty->manager_last_name . "\n";
    echo "Business: " . $bookervilleProperty->business_name . "\n";
    echo "Amenities Count: " . (is_array($bookervilleProperty->amenities) ? count($bookervilleProperty->amenities) : 0) . "\n";
    echo "Photos Count: " . (is_array($bookervilleProperty->photos) ? count($bookervilleProperty->photos) : 0) . "\n";
} else {
    echo "Nenhuma propriedade Bookerville encontrada\n";
}

// Contar propriedades por fonte
$bookerville = Property::where('source', 'bookerville')->count();
$airbnb = Property::where('source', 'airbnb')->count();
$total = Property::count();

echo "\n=== ESTATÍSTICAS ===\n";
echo "Total: $total\n";
echo "Bookerville: $bookerville\n"; 
echo "Airbnb: $airbnb\n";