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
    protected $signature = 'generate-ids {--truncate} {--batch-size=10000} {--limit=' . self::STEAM_ACCOUNT_MAX_NUM . '} {--start-batch=1}';

    public function handle()
    {
        if($this->option('truncate')) {
            DB::table('translate')->truncate();
        }

        $startTimer = microtime(true);

        $total = env('LIMIT', $this->option('limit'));
        $batchSize = env('BATCH_SIZE', $this->option('batch-size'));
        $count = env('START_BATCH', $this->option('start-batch'));

        $while = $total;

        while($while > 0) {

            $todo = ($while - $batchSize);

            $this->batch(
                $count,
                ($count * $batchSize),
                $batchSize,
                ($todo <= 0 ? $startTimer : false)
            );

            $while = $todo;

            $count++;
        }

        $endTimer = microtime(true);

        $duration = $endTimer - $startTimer;

        $this->info('Duration: ' . number_format($duration) . ' seconds');
        $this->info('Queued batches: ' . $count);
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
