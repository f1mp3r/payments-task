<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use CommissionTask\Service\CurrencyConverter;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{
    public function testConvertToEur(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockResponse = new Response(200, [], json_encode([
            'rates' => ['EUR' => 0.85]
        ]));

        $mockClient->expects($this->once())
            ->method('get')
            ->with('', ['query' => ['base' => 'USD', 'symbols' => 'EUR']])
            ->willReturn($mockResponse);

        $converter = new CurrencyConverter('dummy-api-key', $mockClient);

        $amountInEur = $converter->convertToEur(100.0, 'USD');
        $this->assertEqualsWithDelta(117.647, $amountInEur, 0.001);
    }

    public function testConvertToEurWithMissingRate(): void
    {
        $mockClient = $this->createMock(Client::class);

        $mockResponse = new Response(200, [], json_encode([
            'rates' => []
        ]));

        $mockClient->expects($this->once())
            ->method('get')
            ->with('', ['query' => ['base' => 'USD', 'symbols' => 'EUR']])
            ->willReturn($mockResponse);

        $converter = new CurrencyConverter('dummy-api-key', $mockClient);

        $this->expectException(Exception::class);
        $converter->convertToEur(100.0, 'USD');
    }
}