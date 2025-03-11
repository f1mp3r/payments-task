<?php

namespace CommissionTask\Calculator;

use CommissionTask\Model\Operation;

readonly class CommissionCalculator
{
    public function __construct(
        private DepositCalculator          $depositCalculator,
        private WithdrawPrivateCalculator  $withdrawPrivateCalculator,
        private WithdrawBusinessCalculator $withdrawBusinessCalculator,
    ) {
    }

    public function calculate(Operation $operation): string
    {
        $fee = match (true) {
            $operation->getType() === 'deposit' => $this->depositCalculator->calculate($operation),
            $operation->getType() === 'withdraw' && $operation->getUserType() === 'private' => $this->withdrawPrivateCalculator->calculate($operation),
            $operation->getType() === 'withdraw' && $operation->getUserType() === 'business' => $this->withdrawBusinessCalculator->calculate($operation),
        };

        return number_format($fee, $this->getDecimalPlaces($operation->getCurrency()), '.', '');
    }

    private function getDecimalPlaces(string $currency): int
    {
        return in_array($currency, ['JPY']) ? 0 : 2;
    }
}