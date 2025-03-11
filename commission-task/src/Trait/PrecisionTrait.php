<?php

namespace CommissionTask\Trait;

trait PrecisionTrait
{
    private function getPrecision(string $currency): int
    {
        return in_array($currency, ['JPY']) ? 1 : 100;
    }
}