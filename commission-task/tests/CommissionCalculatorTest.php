<?php

use CommissionTask\Calculator\CommissionCalculator;
use CommissionTask\Calculator\DepositCalculator;
use CommissionTask\Calculator\WithdrawBusinessCalculator;
use CommissionTask\Calculator\WithdrawPrivateCalculator;
use CommissionTask\Model\Operation;
use CommissionTask\Service\CurrencyConverter;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    public function testExampleInput(): void
    {
        $mockConverter = $this->createMock(CurrencyConverter::class);

        $mockConverter->method('convertToEur')
            ->willReturnCallback(function ($amount, $currency) {
                if ($currency === 'USD') {
                    return $amount / 1.1497;
                } elseif ($currency === 'JPY') {
                    return $amount / 129.53;
                }
                return $amount;
            });

        $mockConverter->method('convertFromEur')
            ->willReturnCallback(function ($amountEur, $currency) {
                if ($currency === 'USD') {
                    return $amountEur * 1.1497;
                } elseif ($currency === 'JPY') {
                    return $amountEur * 129.53;
                }
                return $amountEur;
            });

        $depositCalculator = new DepositCalculator();
        $withdrawPrivateCalculator = new WithdrawPrivateCalculator($mockConverter);
        $withdrawBusinessCalculator = new WithdrawBusinessCalculator();

        $calculator = new CommissionCalculator(
            $depositCalculator,
            $withdrawPrivateCalculator,
            $withdrawBusinessCalculator
        );

        // Define test operations
        $operations = [
            new Operation('2014-12-31', 4, 'private', 'withdraw', 1200.00, 'EUR'),
            new Operation('2015-01-01', 4, 'private', 'withdraw', 1000.00, 'EUR'),
            new Operation('2016-01-05', 4, 'private', 'withdraw', 1000.00, 'EUR'),
            new Operation('2016-01-05', 1, 'private', 'deposit', 200.00, 'EUR'),
            new Operation('2016-01-06', 2, 'business', 'withdraw', 300.00, 'EUR'),
            new Operation('2016-01-06', 1, 'private', 'withdraw', 30000.00, 'JPY'),
            new Operation('2016-01-07', 1, 'private', 'withdraw', 1000.00, 'EUR'),
            new Operation('2016-01-07', 1, 'private', 'withdraw', 100.00, 'USD'),
            new Operation('2016-01-10', 1, 'private', 'withdraw', 100.00, 'EUR'),
            new Operation('2016-01-10', 2, 'business', 'deposit', 10000.00, 'EUR'),
            new Operation('2016-01-10', 3, 'private', 'withdraw', 1000.00, 'EUR'),
            new Operation('2016-02-15', 1, 'private', 'withdraw', 300.00, 'EUR'),
            new Operation('2016-02-19', 5, 'private', 'withdraw', 3000000.00, 'JPY'),
        ];

        $expected = [
            '0.60',
            '3.00',
            '0.00',
            '0.06',
            '1.50',
            '0',
            '0.70',
            '0.30',
            '0.30',
            '3.00',
            '0.00',
            '0.00',
            '8612',
        ];

        $results = array_map(fn ($op) => $calculator->calculate($op), $operations);
        $this->assertEquals($expected, $results);
    }
}