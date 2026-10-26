<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\Home;
use Config\Services;

class TestHomeEndpoint extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:home-endpoint';
    protected $description = 'Test datasetStats response for dataset 24 and 16';

    public function run(array $params)
    {
        $home = new Home();
        $home->initController(Services::request(), Services::response(), Services::logger());

        CLI::write("Testing datasetStats(24)...", 'yellow');
        $res24 = $home->datasetStats(24);
        $body24 = json_decode($res24->getBody(), true);
        CLI::write("Status: " . ($body24['dataset']['nama_dataset'] ?? 'unknown'));
        CLI::write("Total cases: " . ($body24['total_cases'] ?? 0));
        CLI::write("Total rows: " . ($body24['total_rows'] ?? 0));
        CLI::write("Top diagnosis count: " . count($body24['top_diagnosis'] ?? []));
        if (!empty($body24['top_diagnosis'])) {
            CLI::write("Top 3 diagnosis: " . json_encode(array_slice($body24['top_diagnosis'], 0, 3)));
        }

        CLI::write("Testing datasetStats(16)...", 'yellow');
        $res16 = $home->datasetStats(16);
        $body16 = json_decode($res16->getBody(), true);
        CLI::write("Status: " . ($body16['dataset']['nama_dataset'] ?? 'unknown'));
        CLI::write("Total cases: " . ($body16['total_cases'] ?? 0));
        CLI::write("Total rows: " . ($body16['total_rows'] ?? 0));
        CLI::write("Top diagnosis count: " . count($body16['top_diagnosis'] ?? []));
    }
}
