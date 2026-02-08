<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class DebugPropertyPhotos extends Command
{
    protected $signature = 'debug:property-photos {property_id}';
    protected $description = 'Debug: show photos for a given property_id';

    public function handle()
    {
        $pid = $this->argument('property_id');
        $p = Property::where('property_id', $pid)->first();
        if (! $p) {
            $this->error("Property not found: {$pid}");
            return 1;
        }

        $this->line('property_id: ' . $p->property_id);
        $this->line('photos count: ' . (is_array($p->photos) ? count($p->photos) : 0));
        $this->line('photos:');
        if (is_array($p->photos)) {
            foreach ($p->photos as $i => $ph) {
                $this->line("  [{$i}] {$ph}");
            }
        }

        return 0;
    }
}
