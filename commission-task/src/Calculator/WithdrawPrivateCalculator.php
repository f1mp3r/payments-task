<?php

namespace CommissionTask\Calculator;

use CommissionTask\Model\Operation;
use CommissionTask\Model\WeeklyLimit;
use CommissionTask\Service\CurrencyConverter;
use CommissionTask\Trait\PrecisionTrait;

class WithdrawPrivateCalculator
{
    use PrecisionTrait;

    private const float WITHDRAW_FEE_RATE = 0.003;
    private const float FREE_WEEKLY_LIMIT_EUR = 1000.00;
    private const int FREE_WEEKLY_OPERATIONS = 3;

    private array $weeklyLimits = [];

    public function __construct(private readonly CurrencyConverter $converter)
    {
    }

    public function calculate(Operation $operation): float
    {
        if ($operation->getUserType() !== 'private' || $operation->getType() !== 'withdraw') {
            return 0.0;
        }

        $weekNumber = $operation->getWeekNumber();
        $userId = $operation->getUserId();

        if (!isset($this->weeklyLimits[$userId][$weekNumber])) {
            $this->weeklyLimits[$userId][$weekNumber] = new WeeklyLimit();
        }

        /** @var WeeklyLimit $weeklyLimit */
        $weeklyLimit = $this->weeklyLimits[$userId][$weekNumber];
        $amountInEur = $this->converter->convertToEur($operation->getAmount(), $operation->getCurrency());
        $operationCount = $weeklyLimit->getOperationCount();
        $totalAmountEurBefore = $weeklyLimit->getTotalAmountEur();

        $fee = 0.0;

        if ($operationCount >= self::FREE_WEEKLY_OPERATIONS) {
            $fee = $operation->getAmount() * self::WITHDRAW_FEE_RATE;
        } else {
            $remainingFreeEur = max(0, self::FREE_WEEKLY_LIMIT_EUR - $totalAmountEurBefore);
            if ($amountInEur > $remainingFreeEur) {
                $taxableAmountEur = $amountInEur - $remainingFreeEur;
                $taxableAmount = $this->converter->convertFromEur($taxableAmountEur, $operation->getCurrency());
                $fee = $taxableAmount * self::WITHDRAW_FEE_RATE;
            }
        }

        $weeklyLimit->addOperation($amountInEur);
        return ceil($fee * $this->getPrecision($operation->getCurrency())) / $this->getPrecision($operation->getCurrency());
    }
}