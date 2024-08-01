<?php

namespace Tests\Unit;

use App\Services\QuotationService;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class QuotationServiceTest extends TestCase
{
    private QuotationService $quotationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quotationService = new QuotationService();
    }

    public function testCalculateQuotationSuccessfully()
    {
        $result = $this->quotationService->calculateQuotation(
            ['25', '35'],
            'EUR',
            '2024-08-01',
            '2024-08-10'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('currency_id', $result);
        $this->assertArrayHasKey('quotation_id', $result);
        $this->assertEquals('EUR', $result['currency_id']);

        $expectedTotal = 39.0;          // expected age 30 and 31 10 days (3 * 0.6 * 10) + (3 * 0.7 * 10) = 18 + 21 = 39

        $this->assertEquals($expectedTotal, $result['total']);
    }

    public function testCalculateQuotationWithInvalidCurrency()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Currency Type');

        $this->quotationService->calculateQuotation(
            ['25'],
            'JPY',
            '2024-08-01',
            '2024-08-10'
        );
    }

    public function testCalculateQuotationWithInvalidDateFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        $this->quotationService->calculateQuotation(
            ['25'],
            'EUR',
            '2024/08/01',
            '2024-08-10'
        );
    }

    public function testCalculateQuotationWithPastStartDate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Start date must not be in the past');

        $pastDate = Carbon::yesterday()->format('Y-m-d');
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $this->quotationService->calculateQuotation(
            ['25'],
            'EUR',
            $pastDate,
            $futureDate
        );
    }

    public function testCalculateQuotationWithInvalidAge()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Age must be between 18 and 70');

        $this->quotationService->calculateQuotation(
            ['17'],
            'EUR',
            '2024-08-01',
            '2024-08-10'
        );
    }
}
