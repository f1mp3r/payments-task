<?php

namespace TransactionProcessing;

use InvalidArgumentException;
use JsonException;
use RuntimeException;

class TransactionProcessor
{
    private BinLookupInterface $binLookup;
    private RateProviderInterface $rateProvider;
    private const float EU_COMMISSION_RATE = 0.01;
    private const float NON_EU_COMMISSION_RATE = 0.02;

    public function __construct(BinLookupInterface $binLookup, RateProviderInterface $rateProvider)
    {
        $this->binLookup = $binLookup;
        $this->rateProvider = $rateProvider;
    }

    public function processFile(string $filename): array
    {
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException("Unable to read file: $filename");
        }

        return array_map([$this, 'processTransaction'], $lines);
    }

    public function processTransaction(string $jsonLine): float
    {
        $data = $this->parseTransaction($jsonLine);
        $isEu = $this->binLookup->isEuCard($data['bin']);
        $amountInEur = $this->convertToEur($data['amount'], $data['currency']);
        $commission = $amountInEur * ($isEu ? self::EU_COMMISSION_RATE : self::NON_EU_COMMISSION_RATE);

        return $this->ceilToCents($commission);
    }

    private function parseTransaction(string $jsonLine): array
    {
        try {
            $data = json_decode($jsonLine, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Invalid JSON format: " . $e->getMessage());
        }

        return [
            'bin' => $data['bin'],
            'amount' => (float)$data['amount'],
            'currency' => $data['currency']
        ];
    }

    private function convertToEur(float $amount, string $currency): float
    {
        if ($currency === 'EUR') {
            return $amount;
        }

        $rate = $this->rateProvider->getRate($currency);
        if ($rate <= 0) {
            throw new RuntimeException("Invalid exchange rate for $currency");
        }

        return $amount / $rate;
    }

    private function ceilToCents(float $amount): float
    {
        return ceil($amount * 100) / 100;
    }
}
