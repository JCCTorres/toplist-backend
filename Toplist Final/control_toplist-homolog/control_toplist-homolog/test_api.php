<?php

use App\Services\SyncService;
use App\Services\BookervilleService;

$bookervilleService = new BookervilleService();
$syncService = new SyncService($bookervilleService, null);

// Testar uma propriedade do Bookerville
$result = $syncService->getAllPropertiesCards('resort', 1);

echo "=== TESTE API CARDS - RESORT ===\n";
echo json_encode($result, JSON_PRETTY_PRINT);

echo "\n\n=== TESTE API CARDS - PROPERTY ===\n";
$result2 = $syncService->getAllPropertiesCards('property', 1);
echo json_encode($result2, JSON_PRETTY_PRINT);