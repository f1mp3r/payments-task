#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use CommissionTask\Calculator\CommissionCalculator;
use CommissionTask\Calculator\DepositCalculator;
use CommissionTask\Calculator\WithdrawBusinessCalculator;
use CommissionTask\Calculator\WithdrawPrivateCalculator;
use CommissionTask\Service\CsvReader;
use CommissionTask\Service\CurrencyConverter;
use Dotenv\Dotenv;

if ($argc !== 2) {
    print("Usage: php script.php <csv_file>\n");
    exit(1);
}

$dotenv = Dotenv::createImmutable(realpath(__DIR__));
$dotenv->load();
$apiKey = $_ENV['EXCHANGE_API_KEY'];

if (!$apiKey) {
    print("Error: Missing EXCHANGE_API_KEY in .env file\n");
    exit(1);
}

$csvFile = $argv[1];
$csvReader = new CsvReader($csvFile);
$currencyConverter = new CurrencyConverter($apiKey);
$calculator = new CommissionCalculator(
    new DepositCalculator(),
    new WithdrawPrivateCalculator($currencyConverter),
    new WithdrawBusinessCalculator(),
);

foreach ($csvReader->readOperations() as $operation) {
    echo $calculator->calculate($operation) . "\n";
}