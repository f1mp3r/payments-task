<?php
require_once __DIR__ . '/../vendor/autoload.php';

use TransactionProcessing\TransactionProcessor;
use TransactionProcessing\BinListLookup;
use TransactionProcessing\ExchangeRatesApiProvider;
use Dotenv\Dotenv;

if (count($argv) < 2) {
    print("Usage: php {$argv[0]} <input_file>\n");
    exit(1);
}

$dotenv = Dotenv::createImmutable(realpath(__DIR__ . '/..'));
$dotenv->load();

$exchangeApiKey = $_ENV['EXCHANGE_API_KEY'] ?? getenv('EXCHANGE_API_KEY');
$binApiKey = $_ENV['BIN_API_KEY'] ?? getenv('BIN_API_KEY');

if (!$exchangeApiKey || !$binApiKey) {
    exit("Error: Missing EXCHANGE_API_KEY or BIN_API_KEY in .env file\n");
}

$processor = new TransactionProcessor(
    new BinListLookup($binApiKey),
    new ExchangeRatesApiProvider($exchangeApiKey)
);

try {
    $results = $processor->processFile($argv[1]);
    echo implode("\n", $results) . "\n";
} catch (Exception $e) {
    exit("Error: " . $e->getMessage() . "\n");
}