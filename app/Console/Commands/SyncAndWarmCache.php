<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncAndWarmCache extends Command
{
    protected $signature = 'bookerville:sync-and-warm';

    protected $description = 'Sync all Bookerville properties to DB (including rates) and warm the home cards response cache';

    public function handle(SyncService $syncService): int
    {
        $this->info('Starting Bookerville sync...');
        Log::info('[sync-and-warm] Starting sync + cache warm');

        // 1. Sync all properties (rates & fees now included in details)
        $result = $syncService->syncAllProperties();

        if (!$result['success']) {
            $this->error('Sync failed: ' . ($result['message'] ?? 'unknown error'));
            Log::error('[sync-and-warm] Sync failed', $result);
            return Command::FAILURE;
        }

        $this->info("Synced {$result['count']} properties.");

        if (!empty($result['errors'])) {
            $this->warn(count($result['errors']) . ' properties had errors.');
        }

        // 2. Clear home_cards_response cache for common limit values
        $limits = [6, 10, 12, 20, 50, 100];
        foreach ($limits as $limit) {
            Cache::forget("home_cards_response_{$limit}");
        }
        $this->info('Cleared home_cards_response cache keys.');

        // 3. Warm cache by dispatching an internal request to the home cards endpoint.
        //    This populates the Cache::remember() inside getHomeCards() with the full card shape.
        try {
            $request = \Illuminate\Http\Request::create('/api/bookerville/home-cards?limit=100', 'GET');
            $response = app()->handle($request);
            $status = $response->getStatusCode();

            if ($status === 200) {
                $this->info('Cache warmed via internal /api/bookerville/home-cards request.');
            } else {
                $this->warn("Cache warm request returned HTTP {$status} (non-fatal).");
            }
        } catch (\Exception $e) {
            $this->warn('Cache warming failed (non-fatal): ' . $e->getMessage());
            Log::warning('[sync-and-warm] Cache warming failed', ['error' => $e->getMessage()]);
        }

        Log::info('[sync-and-warm] Completed successfully');
        $this->info('Done.');

        return Command::SUCCESS;
    }
}
