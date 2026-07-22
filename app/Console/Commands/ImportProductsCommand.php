<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ImportProductsCommand extends Command
{
    protected $signature = 'import:products {file : The path to the Excel file} {--chunk=100 : Number of records per chunk}';

    protected $description = 'Import products from Excel file';

    public function handle()
    {
        $filePath = $this->argument('file');
        $chunkSize = $this->option('chunk');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Starting product import from: {$filePath}");
        $this->info("Chunk size: {$chunkSize}");

        try {
            Excel::import(new ProductsImport(), $filePath);
            
            $this->info('Products imported successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            Log::error('Import Command Error: ' . $e->getMessage());
            return 1;
        }
    }
}
