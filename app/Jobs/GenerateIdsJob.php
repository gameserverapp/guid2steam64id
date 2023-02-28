<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;

class GenerateIdsJob extends Job
{
    private $startId;
    private $batchSize;
    private $batchId;
    private $startTime;

    public function __construct(
        $batchId,
        $startId,
        $batchSize,
        $startTime
    ) {
        $this->batchId = $batchId;
        $this->startId = $startId;
        $this->batchSize = $batchSize;
        $this->startTime = $startTime;
    }

    public function handle()
    {
        if(env('APP_DEBUG')) {
            $startTimer = microtime(true);
        }

        $data = [];

        for ($i = $this->startId; $i < ($this->startId + $this->batchSize); $i++) {

            $steam64id = $this->toCommunityID($i);
            $guid = $this->toGUID($steam64id);

            $data[] = [
                'id' => $i,
                'steam_id' => $steam64id,
                'guid' => $guid
            ];
        }

        if(env('APP_DEBUG')) {
            $startDbTimer = microtime(true);
        }

        DB::table('translate')->insertOrIgnore($data);

        if(env('APP_DEBUG')) {

            $endTimer = microtime(true);

            $duration = $endTimer - $startTimer;
            $durationDb = $endTimer - $startDbTimer;

            print(
                'Batch [' . $this->batchId . ']' . "\n" .
                'Range: ' . $this->startId . ' - ' . ($this->startId + $this->batchSize) . "\n" .
                'Generating : ' . ( $duration - $durationDb ) . 'seconds' . "\n" .
                'DB insert: ' . $durationDb . ' seconds' . "\n" .
                'Total: ' . $duration . ' seconds' . "\n\n"
            );
        }

        if($this->startTime) {
            print(
                'Total duration: ' . (microtime(true) - $this->startTime) . ' seconds' . "\n\n"
            );
        }
    }

    private function toCommunityID($id)
    {
        return bcadd($id, '76561197960265728');
    }

    private function toGUID($steamID)
    {
        $temp = '';

        for ($i = 0; $i < 8; $i++) {
            $temp .= chr($steamID & 0xFF);
            $steamID >>= 8;
        }

        return md5('BE' . $temp);
    }
}
