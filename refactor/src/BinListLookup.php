<?php
namespace TransactionProcessing;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use RuntimeException;

class BinListLookup implements BinLookupInterface
{
    private const string API_URL = 'https://data.handyapi.com/bin/';
    private Client $httpClient;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client();
    }

    public function isEuCard(string $bin): bool
    {
        $options = [
            'headers' => [
                'x-api-key' => $this->apiKey
            ]
        ];

        try {
            $response = $this->httpClient->get(self::API_URL . $bin, $options);
            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return $this->isEuCountry($data['Country']['A2']);
        } catch (RequestException $e) {
            throw new RuntimeException("Failed to lookup BIN $bin: " . $e->getMessage());
        } catch (JsonException $e) {
            throw new RuntimeException("Invalid BIN lookup response: " . $e->getMessage());
        }
    }

    private function isEuCountry(string $countryCode): bool
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
            'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];

        return in_array($countryCode, $euCountries, true);
    }
}