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
    protected $signature = 'generate-ids';

    public function handle()
    {
        //DB::table('translate')->truncate();

        $total = env('LIMIT', 700000000);
        $batchSize = env('BATCH_SIZE', 10000);

        $startTimer = microtime(true);

        $count = env('START_BATCH', 1);
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
        $this->info('Batches: ' . $count);
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
