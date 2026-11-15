<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use Src\DataImport\Application\SyncFormasiFromBkn;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$baris = json_decode((string) file_get_contents(dirname(__DIR__).'/Fixtures/bkn-cpns-sintetis.json'), true)['data'];

Http::fake(['*' => Http::response(['status' => 200, 'error' => false, 'message' => 'success', 'data' => $baris])]);

$hasil = $app->make(SyncFormasiFromBkn::class)->handle(null);

echo $hasil->pesan, PHP_EOL;
