<?php
namespace TransactionProcessing;

interface RateProviderInterface
{
    public function getRate(string $currency): float;
}