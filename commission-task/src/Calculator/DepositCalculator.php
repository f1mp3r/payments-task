<?php

namespace CommissionTask\Calculator;

use CommissionTask\Model\Operation;
use CommissionTask\Trait\PrecisionTrait;

class DepositCalculator
{
    use PrecisionTrait;

    private const float DEPOSIT_FEE_RATE = 0.0003;

    public function calculate(Operation $operation): float
    {
        $fee = $operation->getAmount() * self::DEPOSIT_FEE_RATE;
        return ceil($fee * $this->getPrecision($operation->getCurrency())) / $this->getPrecision($operation->getCurrency());
    }
}