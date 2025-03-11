<?php

namespace TransactionProcessing;

interface BinLookupInterface
{
    public function isEuCard(string $bin): bool;
}