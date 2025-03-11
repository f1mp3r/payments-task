<?php

namespace CommissionTask\Service;

use CommissionTask\Model\Operation;

class CsvReader
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return Operation[]
     */
    public function readOperations(): iterable
    {
        $file = fopen($this->filePath, 'r');
        while (($row = fgetcsv($file, escape: '\\')) !== false) {
            yield new Operation(
                $row[0], // date
                (int)$row[1], // userId
                $row[2], // userType
                $row[3], // type
                (float)$row[4], // amount
                $row[5] // currency
            );
        }
        fclose($file);
    }
}