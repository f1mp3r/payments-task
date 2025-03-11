<?php

namespace CommissionTask\Calculator;

use CommissionTask\Model\Operation;
use CommissionTask\Trait\PrecisionTrait;

class WithdrawBusinessCalculator
{
    use PrecisionTrait;

    private const float WITHDRAW_FEE_RATE = 0.005;

    public function calculate(Operation $operation): float
    {
        $fee = $operation->getAmount() * self::WITHDRAW_FEE_RATE;
        return ceil($fee * $this->getPrecision($operation->getCurrency())) / $this->getPrecision($operation->getCurrency());
    }
}