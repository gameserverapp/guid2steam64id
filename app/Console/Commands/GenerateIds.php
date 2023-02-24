<?php

namespace App\Console\Commands;

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

        $total = 700000000;
        $batchSize = 4000;

        $startTimer = microtime(true);

        $count = 1;
        $while = $total;

        while($while > 0) {

            $this->batch(
                ($count * $batchSize),
                $batchSize
            );

            $while = $while - $batchSize;
            $count++;
        }

        $endTimer = microtime(true);

        $duration = $endTimer - $startTimer;

        $this->info('Duration: ' . $duration);
    }

    private function batch($startId, $itemsPerBatch)
    {
        $startTimer = microtime(true);

        $data = [];

        for ($i = $startId; $i < ($startId + $itemsPerBatch); $i++) {

            $steam64id = $this->toCommunityID($i);
            $guid = $this->toGUID($steam64id);

            $data[] = [
                'steam_id' => $steam64id,
                'guid' => $guid
            ];
        }

        DB::table('translate')->insert($data);

        $endTimer = microtime(true);

        $duration = $endTimer - $startTimer;

        $this->info('Batch duration: ' . $duration);
    }

    private function toCommunityID($id)
    {
        return bcadd($id, '76561197960265728');
    }

    private function toGUID($id)
    {
        $temp = '';

        for ($i = 0; $i < 8; $i++) {
            $temp .= chr($id & 0xFF);
        }

        return md5('BE' . $temp);
    }
}
