<?php
require 'bootstrap/app.php';

$app = app();
$service = new \App\Services\SyncService($app->make(\App\Services\BookervilleService::class));

// Testar uma propriedade do Bookerville
$result = $service->getAllPropertiesCards('resort', 1);
echo "=== TESTE DE UMA PROPRIEDADE RESORT ===\n";
echo json_encode($result, JSON_PRETTY_PRINT);

echo "\n\n=== TESTE DE UMA PROPRIEDADE NORMAL ===\n";
$result2 = $service->getAllPropertiesCards('property', 1);
echo json_encode($result2, JSON_PRETTY_PRINT);

echo "\n\n=== ESTATÍSTICAS ===\n";
$stats = $service->getSyncStats();
echo json_encode($stats, JSON_PRETTY_PRINT);