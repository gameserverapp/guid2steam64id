# BattlEye GUID to Steam64 ID (and back)

A simple micro service helps setup a self-hosted REST API to translate BattlEye GUIDs into Steam 64 IDs. You build your own database.

## 1. Installation
You need to make sure you setup a hosting environment that can run Laravel. See requirements below.

1. Clone in your webroot `git clone git@github.com:gameserverapp/guid2steam64id.git .`.
2. Run `composer install` to install required packages.
3. Configure your `.env` file and confirm a working DB connection.
   1. Setup database (MariaDB)
   2. Configure queue (Beanstalk)
4. Setup Supervisor worker (see `{APP ROOT}/files/supervisor_process`)
5. Generate database  (next step).

## 2. Build your database tables
Your database will start empty. This micro service comes with a generator to populate your database.

Depending on your hardware, it may take several hours to build your tables. When using queues this can be done in the background.

`php artisan generate-ids`


#### Truncate database before starting
`php artisan generate-ids --truncate`

#### Specify batch sizes
Allows you to tune the query batch size to your setup. 

`php artisan generate-ids --batch-size=2000`

#### Override the total number of records generated
`php artisan generate-ids --limit=10000000`

#### Start from a specific batch
`php artisan generate-ids --start-batch=1`

### Example output
This example was generated using `QUEUE_CONNECTION=sync`. When using the Beanstalk queue the output will be different.
```
$ php artisan generate-ids --truncate --batch-size=2000 --limit=1000000
string(37) "Batch [1] Duration: 0.097071170806885"
string(36) "Batch [2] Duration: 0.13755321502686"
string(37) "Batch [3] Duration: 0.089584827423096"
....
....
string(39) "Batch [498] Duration: 0.041013956069946"
string(39) "Batch [499] Duration: 0.054871082305908"
string(39) "Batch [500] Duration: 0.039829969406128"
Duration: 26 seconds
Queued batches: 501
```

## 3. Consume API
When you configured an `API_KEY=` in your `.env` file, make sure to always set a header `x-api-key: {API_KEY}`. The header is only required when `API_KEY=` is not null.

#### Get GUID for Steam 64 ID

`GET http://localhost/steamid/76561198023513722`

Response:
```http
{"guid":"cf165a55c873587ecd183628395aeda2"}
```

#### Get Steam 64 ID for GUID

`GET http://localhost/guid/cf165a55c873587ecd183628395aeda2`

Response:
```http
{"steam_id":76561198023513722}
```

___

## Requirements
To get it running, you need to setup a LEMP / LAMP server that can run Laravel.

- PHP 7.3
- NGINX (or Apache)
- MariabDB (or MySQL)
- Composer
- Supervisor
- Beanstalkd

### Machine specs
Generating all records for the database takes time. With powerful hardware you can speed this up.

Tested hardware:
- 16 vCPUs
- 32GB RAM
- SSD disk

Total generation time: 
___

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to GameServerApp at support@gameserverapp.com. All security vulnerabilities will be promptly addressed.

## License

This GUID2Steam64ID micro service is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

___

## Official Lumen & Laravel Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).
