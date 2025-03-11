<?php

namespace CommissionTask\Model;

use DateMalformedStringException;
use DateTime;

class Operation
{
    private string $date;
    private int $userId;
    private string $userType;
    private string $type;
    private float $amount;
    private string $currency;

    public function __construct(string $date, int $userId, string $userType, string $type, float $amount, string $currency)
    {
        $this->date = $date;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->type = $type;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getUserId(): int { return $this->userId; }
    public function getUserType(): string { return $this->userType; }
    public function getType(): string { return $this->type; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }

    /**
     * @throws DateMalformedStringException
     */
    public function getWeekNumber(): string
    {
        $date = new DateTime($this->date);
        $dayOfWeek = (int) $date->format('N');

        if ($dayOfWeek > 1) {
            $date->modify('-' . ($dayOfWeek - 1) . ' days');
        }

        return $date->format('Y-m-d');
    }
}