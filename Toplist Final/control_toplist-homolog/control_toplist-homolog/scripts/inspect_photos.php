<?php
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;

$ids = ['11684', '10705'];
foreach ($ids as $id) {
    $p = Property::where('property_id', $id)->first();
    echo "PROPERTY: {$id}\n";
    if (!$p) { echo "NOT FOUND\n\n"; continue; }
    echo json_encode($p->photos) . "\n\n";
}
