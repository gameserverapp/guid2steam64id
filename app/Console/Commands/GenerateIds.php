<?php

namespace App\Console\Commands;

use App\Jobs\GenerateIdsJob;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class GenerateIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-ids {--truncate} {--batch-size=10000} {--limit=700000000} {--start-batch=1}';

    public function handle()
    {
        if($this->option('truncate')) {
            DB::table('translate')->truncate();
        }

        $total = env('LIMIT', $this->option('limit'));
        $batchSize = env('BATCH_SIZE', $this->option('batch-size'));

        $startTimer = microtime(true);

        $count = env('START_BATCH', $this->option('start-batch'));
        $while = $total;

        while($while > 0) {

            $this->batch(
                $count,
                ($count * $batchSize),
                $batchSize
            );

            $while = $while - $batchSize;
            $count++;
        }

        $endTimer = microtime(true);

        $duration = $endTimer - $startTimer;

        $this->info('Duration: ' . $duration);
        $this->info('Queued batches: ' . $count);
    }

    private function batch($batchId, $startId, $itemsPerBatch)
    {
        dispatch(
            new GenerateIdsJob(
                $batchId,
                $startId,
                $itemsPerBatch
            )
        );
    }
}
