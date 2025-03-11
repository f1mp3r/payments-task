<?php

namespace CommissionTask\Model;

class WeeklyLimit
{
    private int $operationCount = 0;
    private float $totalAmountEur = 0.0;

    public function addOperation(float $amountEur): void
    {
        $this->operationCount++;
        $this->totalAmountEur += $amountEur;
    }

    public function getOperationCount(): int
    {
        return $this->operationCount;
    }

    public function getTotalAmountEur(): float
    {
        return $this->totalAmountEur;
    }
}