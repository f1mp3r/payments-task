<?php

namespace CommissionTask\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CurrencyConverter
{
    private string $apiKey;
    private string $apiUrl = 'https://api.apilayer.com/exchangerates_data/latest';
    private Client $client;

    /**
     * Constructor to initialize the API key and Guzzle client.
     *
     * @param string $apiKey The API key for authentication
     */
    public function __construct(string $apiKey, ?Client $client = null) {
        $this->apiKey = $apiKey;
        $this->client = $client ?? new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'apikey' => $this->apiKey,
            ],
        ]);
    }

    /**
     * Convert an amount to EUR from another currency.
     *
     * @param float $amount The amount to convert
     * @param string $fromCurrency The source currency (e.g., USD)
     * @return float The amount in EUR
     * @throws GuzzleException
     */
    public function convertToEur(float $amount, string $fromCurrency): float
    {
        if ($fromCurrency === 'EUR') {
            return $amount; // No conversion needed if already in EUR
        }

        $rate = $this->getExchangeRate($fromCurrency, 'EUR');
        return $amount / $rate;
    }

    /**
     * Convert an amount from EUR to another currency.
     *
     * @param float $amountEur The amount in EUR
     * @param string $toCurrency The target currency (e.g., USD)
     * @return float The converted amount
     * @throws GuzzleException
     */
    public function convertFromEur(float $amountEur, string $toCurrency): float
    {
        if ($toCurrency === 'EUR') {
            return $amountEur; // No conversion needed if target is EUR
        }

        $rate = $this->getExchangeRate('EUR', $toCurrency);
        return $amountEur * $rate;
    }

    /**
     * Fetch the exchange rate between two currencies using the API.
     *
     * @param string $base The base currency (e.g., USD)
     * @param string $target The target currency (e.g., EUR)
     * @return float The exchange rate
     * @throws GuzzleException
     */
    private function getExchangeRate(string $base, string $target): float
    {
        $query = [
            'base' => $base,
            'symbols' => $target,
        ];

        $response = $this->client->get('', ['query' => $query]);
        $data = json_decode($response->getBody(), true);

        if (isset($data['rates'][$target]) && is_numeric($data['rates'][$target])) {
            return (float) $data['rates'][$target];
        }

        throw new Exception("Exchange rate for {$base} to {$target} not found in API response.");
    }
}