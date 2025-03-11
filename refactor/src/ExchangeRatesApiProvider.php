<?php
namespace TransactionProcessing;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use RuntimeException;

class ExchangeRatesApiProvider implements RateProviderInterface
{
    private const string API_URL = 'https://api.apilayer.com/exchangerates_data/latest';
    private Client $httpClient;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client();
    }

    public function getRate(string $currency): float
    {
        $options = [
            'headers' => [
                'apikey' => $this->apiKey
            ],
            'query' => [
                'symbols' => 'EUR',
                'base' => $currency,
            ]
        ];

        try {
            $response = $this->httpClient->get(self::API_URL, $options);
            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $convertedAmount = $data['rates']['EUR'];
            if ($convertedAmount <= 0) {
                throw new RuntimeException("Invalid exchange rate result for $currency");
            }

            // Return the rate to convert FROM the currency TO EUR (inverse of the conversion rate)
            return 1.0 / $convertedAmount;
        } catch (RequestException|GuzzleException $e) {
            throw new RuntimeException('Failed to fetch exchange rate from apilayer: ' . $e->getMessage());
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON response from apilayer: ' . $e->getMessage());
        }
    }
}