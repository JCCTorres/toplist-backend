<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ClientProperty;

class IterateExcelCommand extends Command
{
    protected $signature = 'excel:iterate {file="Airbnb Listings Data (3).xlsx"} {--limit=0 : Maximum rows to process (0 = all)}';
    protected $description = 'Iterate over an Excel file and write a simple CSV report to storage/logs';

    public function handle()
    {
        $fileArg = $this->argument('file');
        $file = base_path($fileArg);

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $limit = (int) $this->option('limit');

        $this->info('Reading file: ' . $file);

        try {
            // toArray returns an array of sheets, each sheet is array of rows (numeric-indexed arrays)
            $sheets = Excel::toArray(null, $file);
        } catch (\Throwable $e) {
            $this->error('Failed to read Excel file: ' . $e->getMessage());
            return 1;
        }

        if (empty($sheets) || !isset($sheets[0])) {
            $this->error('No sheets or rows found in file');
            return 1;
        }

        $rows = $sheets[0];
        $total = count($rows);
        $this->info("Rows in first sheet: {$total}");

        $reportPath = storage_path('logs/excel_iterate_report_' . date('Ymd_His') . '.csv');
        $rf = fopen($reportPath, 'w');
        fputcsv($rf, ['row', 'data', 'client_property_id', 'client_property_title']);

        // detect header and the index of the Title column (if present)
        $titleIndex = null;
        $hasHeader = false;
        if (!empty($rows) && is_array($rows[0])) {
            $first = $rows[0];
            $lower = array_map(function ($v) {
                return is_null($v) ? '' : strtolower(trim((string)$v));
            }, $first);
            foreach ($lower as $idx => $val) {
                if (in_array($val, ['title', 'titulo', 'name'])) {
                    $titleIndex = $idx;
                    $hasHeader = true;
                    break;
                }
            }
        }

        $processed = 0;

        foreach ($rows as $i => $r) {

            if ($i > 0) {

                $clientPropertyId = ClientProperty::query()->where('title', $r[2])->first();
                if ($clientPropertyId) {
                    $clients[] = $clientPropertyId;
                    $clientPropertyId->update(['airbnb_id' => $r[0]]);
                    $clientPropertyId->save();
                }


                $processed++;
            }
        }
        $this->info("Processed {$processed} rows. Report written to: {$reportPath}");

        fclose($rf);
    }
}
