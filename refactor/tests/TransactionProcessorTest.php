<?php
namespace TransactionProcessing\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TransactionProcessing\ExchangeRatesApiProvider;
use TransactionProcessing\TransactionProcessor;
use TransactionProcessing\BinLookupInterface;
use TransactionProcessing\RateProviderInterface;

class TransactionProcessorTest extends TestCase
{
    public function testEuCardWithEur(): void
    {
        $binLookup = $this->createMock(BinLookupInterface::class);
        $binLookup->method('isEuCard')->willReturn(true);

        $rateProvider = $this->createMock(RateProviderInterface::class);

        $processor = new TransactionProcessor($binLookup, $rateProvider);
        $result = $processor->processTransaction('{"bin":"45717360","amount":"100.00","currency":"EUR"}');

        $this->assertEquals(1.00, $result);
    }

    public function testNonEuCardWithUsd(): void
    {
        $binLookup = $this->createMock(BinLookupInterface::class);
        $binLookup->method('isEuCard')->willReturn(false);

        $rateProvider = $this->createMock(RateProviderInterface::class);
        $rateProvider->method('getRate')->willReturn(1.1);

        $processor = new TransactionProcessor($binLookup, $rateProvider);
        $result = $processor->processTransaction('{"bin":"516793","amount":"50.00","currency":"USD"}');

        $this->assertEquals(0.91, $result);
    }

    public function testInvalidJsonThrowsException(): void
    {
        $processor = new TransactionProcessor(
            $this->createMock(BinLookupInterface::class),
            $this->createMock(RateProviderInterface::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $processor->processTransaction('invalid json');
    }

    public function testExchangeRateWithGuzzle(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('get')->willReturn(
            new Response(200, [], json_encode(['rates' => ['EUR' => 0.91]]))
        );

        $provider = new ExchangeRatesApiProvider('dummy-api-key');
        $reflection = new \ReflectionClass(ExchangeRatesApiProvider::class);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($provider, $mockClient);

        $rate = $provider->getRate('USD');
        $this->assertEqualsWithDelta(1.0989, $rate, 0.0001);
    }
}