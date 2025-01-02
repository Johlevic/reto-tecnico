<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VoucherService;

class TestVoucherExtraction extends Command
{
    protected $signature = 'voucher:extract-test {file}'; // El archivo XML será un argumento
    protected $description = 'Prueba la extracción de datos desde un archivo XML';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: $filePath");
            return;
        }

        $xmlContent = file_get_contents($filePath);
        $service = new VoucherService();
        $data = $service->extractDataFromXml($xmlContent);

        // Inspeccionar los datos extraídos
        dd($data);
    }
}

