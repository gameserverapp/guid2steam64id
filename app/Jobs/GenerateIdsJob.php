<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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

        $csvHeader = [
            'id',
            'steam_id',
            'guid'
        ];

        $f = fopen('php://memory', 'r+');

        fputcsv($f, $csvHeader);

        for ($i = $this->startId; $i < ($this->startId + $this->batchSize); $i++) {

            $steam64id = $this->toCommunityID($i);

            fputcsv($f, [
                $i,
                $steam64id,
                $this->toGUID($steam64id)
            ]);
        }

        rewind($f);
        $data = stream_get_contents($f);

        if(env('APP_DEBUG')) {
            $startDbTimer = microtime(true);
        }

        $path = storage_path('app') . '/batch-' . $this->batchId . '.csv';
        File::put($path, $data);

        $query = "LOAD DATA LOCAL INFILE '" . $path . "' INTO TABLE translate (id, steam_id, guid)";
        DB::connection()->getpdo()->exec($query);

//        DB::table('translate')->insertOrIgnore($data);

        if(env('APP_DEBUG')) {

            $endTimer = microtime(true);

            $duration = $endTimer - $startTimer;
            $durationDb = $endTimer - $startDbTimer;

            print(
                'Batch [' . $this->batchId . ']' . "\n" .
                'Range: ' . $this->startId . ' - ' . ($this->startId + $this->batchSize) . "\n" .
                'Generating : ' . ( $duration - $durationDb ) . ' seconds' . "\n" .
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
