<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class QuotationService
{
    public const FIXED_RATE = 3;
    public const AGE_LOADS = [
        '18-30' => 0.6,
        '31-40' => 0.7,
        '41-50' => 0.8,
        '51-60' => 0.9,
        '61-70' => 1,
    ];

    public function calculateQuotation(array $ages, string $currencyId, string $startDate, string $endDate): array //send to additional validation, calculate, return
    {
        $this->validateInput($ages, $currencyId, $startDate, $endDate);

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $tripLength = $endDate->diffInDays($startDate) + 1; // including the initial day
        $total = 0;
        foreach ($ages as $age) {
            $age = (int)$age; // type casting
            $ageLoad = $this->getAgeLoad($age);
            $total += self::FIXED_RATE * $ageLoad * $tripLength;  //total for each age added to itself
        }

        return [
            'total' => number_format($total, 2, '.', ''),  // 2 decimal places
            'currency_id' => $currencyId,
            'quotation_id' => $this->generateQuotationId(),
           ];
    }

    private function validateInput(array $ages, string $currencyId, string $startDate, string $endDate): void // validate input parms
    {
        $today = Carbon::today();
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        if (!in_array($currencyId, ['EUR', 'GBP', 'USD'])) {   //for api mainly
            throw new InvalidArgumentException('Invalid Currency Type');
        }
        if (!Carbon::hasFormat($startDate, 'Y-m-d') || !Carbon::hasFormat($endDate, 'Y-m-d')) {  //for api mainly
            throw new InvalidArgumentException('Invalid date format, format must be Y-m-d');
        }
        if ($startDateCarbon->lt($today)) {
            throw new InvalidArgumentException('Start date must not be in the past');  //less than
        }

        if ($startDateCarbon->gt($endDateCarbon)) {
            throw new InvalidArgumentException('Start date must be before or equal to end date'); // greater than
        }

        foreach ($ages as $age) {
            if (!ctype_digit($age)) {
                throw new InvalidArgumentException('Age must be a whole number without decimals or special characters');
            }
            $ageValue = (int)$age;
            if ($ageValue < 18 || $ageValue > 70) {
                throw new InvalidArgumentException('Age must be between 18 and 70');
            }
        }
    }

    private function getAgeLoad(int $age): float //determin agebased pricing
    {
        foreach (self::AGE_LOADS as $range => $load) {  //set key value pairs
            [$min, $max] = explode('-', $range); //output array save as min/max
            if ($age >= $min && $age <= $max) {  //check against the age load table
                return $load;
            }
        }

    }

    public function generateQuotationId(): string
    {
        return uniqid('qid', false);
    }
}
