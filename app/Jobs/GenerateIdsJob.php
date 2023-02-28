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

        $f = fopen('php://memory', 'r+');

        fputcsv($f, [
            'id',
            'steam_id',
            'guid'
        ]);

        for ($i = $this->startId; $i < ($this->startId + $this->batchSize); $i++) {

            $steam64id = $this->toCommunityID($i);

            fputcsv($f, [
                    $i,
                    $steam64id,
                    $this->toGUID($steam64id)
                ],
                ','
            );
        }

        rewind($f);
        $data = stream_get_contents($f);

        $path = storage_path('app') . '/batch-' . $this->batchId . '.csv';
        File::put($path, $data);

        if(env('APP_DEBUG')) {
            $startDbTimer = microtime(true);
        }

        $query = 'LOAD DATA CONCURRENT LOCAL INFILE \'' . $path . '\'
                    IGNORE
                    INTO TABLE `translate`
                FIELDS
                    TERMINATED BY ","
                    OPTIONALLY ENCLOSED BY "\""
                LINES
                    TERMINATED BY "\n"
                IGNORE 1 lines
                (id, steam_id, guid);';

        DB::connection()->getpdo()->exec($query);

        File::delete($path);

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
