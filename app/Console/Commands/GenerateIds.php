<?php

namespace App\Console\Commands;

use App\Jobs\GenerateIdsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateIds extends Command
{

    const STEAM_ACCOUNT_MAX_NUM = 1596147994;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-ids {--truncate} {--batch-size=10000} {--limit=' . self::STEAM_ACCOUNT_MAX_NUM . '} {--start-batch=0}';

    public function handle()
    {
        if($this->option('truncate')) {
            DB::table('translate')->truncate();
        }

        $startTimer = microtime(true);

        $total = env('LIMIT', $this->option('limit'));
        $batchSize = env('BATCH_SIZE', $this->option('batch-size'));
        $count = env('START_BATCH', $this->option('start-batch'));

        if($count > 0 and $count > $batchSize) {
            $count = $count / $batchSize;
        }

        $batchCount = $total / $batchSize;

        for($i = $count; $i < $batchCount; $i++) {

            $isLast = ($batchCount-1) == $i;

            $this->batch(
                $i,
                ($i * $batchSize),
                $batchSize,
                ($isLast ? $startTimer : false)
            );
        }

        $endTimer = microtime(true);

        $duration = $endTimer - $startTimer;

        $this->info('Duration: ' . $duration . ' seconds');
        $this->info('Queued batches: ' . $i);
    }

    private function batch(
        $batchId,
        $startId,
        $itemsPerBatch,
        $startTime = false
    ) {
        dispatch(
            new GenerateIdsJob(
                $batchId,
                $startId,
                $itemsPerBatch,
                $startTime
            )
        );
    }
}
