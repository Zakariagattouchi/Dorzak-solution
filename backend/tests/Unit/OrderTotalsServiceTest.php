<?php

namespace Tests\Unit;

use App\Services\OrderTotalsService;
use PHPUnit\Framework\TestCase;

class OrderTotalsServiceTest extends TestCase
{
    private OrderTotalsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderTotalsService;
    }

    private function tax(bool $charge = true, float $rate = 8.5, bool $included = false): array
    {
        return ['charge_sales_tax' => $charge, 'tax_rate' => $rate, 'tax_included_in_price' => $included];
    }

    public function test_subtotal_is_sum_of_line_totals(): void
    {
        $lines = [
            ['unit_price' => 49.99, 'quantity' => 2, 'taxable' => true],
            ['unit_price' => 8.50, 'quantity' => 1, 'taxable' => true],
        ];
        $result = $this->service->compute($lines, 0, $this->tax(charge: false));

        $this->assertSame('108.48', $result['subtotal']);
        $this->assertSame('0.00', $result['tax_amount']);
        $this->assertSame('108.48', $result['total']);
    }

    public function test_tax_applies_only_to_taxable_lines(): void
    {
        $lines = [
            ['unit_price' => 100.00, 'quantity' => 1, 'taxable' => true],
            ['unit_price' => 50.00, 'quantity' => 1, 'taxable' => false],
        ];
        $result = $this->service->compute($lines, 0, $this->tax(rate: 10));

        // Tax only on the 100.00 line.
        $this->assertSame('150.00', $result['subtotal']);
        $this->assertSame('10.00', $result['tax_amount']);
        $this->assertSame('160.00', $result['total']);
    }

    public function test_tax_excluded_adds_tax(): void
    {
        $lines = [['unit_price' => 100.00, 'quantity' => 1, 'taxable' => true]];
        $result = $this->service->compute($lines, 0, $this->tax(rate: 8.5));

        $this->assertSame('8.50', $result['tax_amount']);
        $this->assertSame('108.50', $result['total']);
    }

    public function test_tax_included_extracts_tax_without_changing_total(): void
    {
        $lines = [['unit_price' => 108.50, 'quantity' => 1, 'taxable' => true]];
        $result = $this->service->compute($lines, 0, $this->tax(rate: 8.5, included: true));

        $this->assertSame('108.50', $result['subtotal']);
        $this->assertSame('8.50', $result['tax_amount']); // 108.50 - 108.50/1.085
        $this->assertSame('108.50', $result['total']);    // unchanged
    }

    public function test_charge_disabled_yields_zero_tax(): void
    {
        $lines = [['unit_price' => 100.00, 'quantity' => 1, 'taxable' => true]];
        $result = $this->service->compute($lines, 0, $this->tax(charge: false, rate: 8.5));

        $this->assertSame('0.00', $result['tax_amount']);
        $this->assertSame('100.00', $result['total']);
    }

    public function test_discount_reduces_tax_base_proportionally(): void
    {
        $lines = [['unit_price' => 100.00, 'quantity' => 1, 'taxable' => true]];
        // 10% discount -> taxable base 90 -> tax 9.00 at 10%
        $result = $this->service->compute($lines, 10, $this->tax(rate: 10));

        $this->assertSame('100.00', $result['subtotal']);
        $this->assertSame('9.00', $result['tax_amount']);
        $this->assertSame('99.00', $result['total']); // 100 - 10 + 9
    }

    public function test_discount_is_clamped_to_subtotal(): void
    {
        $lines = [['unit_price' => 20.00, 'quantity' => 1, 'taxable' => false]];
        $result = $this->service->compute($lines, 999, $this->tax(charge: false));

        $this->assertSame('0.00', $result['total']);
    }

    public function test_delivery_fee_is_added_after_tax(): void
    {
        $lines = [['unit_price' => 50.00, 'quantity' => 1, 'taxable' => true]];
        $result = $this->service->compute($lines, 0, $this->tax(rate: 10), deliveryFee: 5.0);

        $this->assertSame('5.00', $result['tax_amount']);
        $this->assertSame('60.00', $result['total']); // 50 + 5 tax + 5 delivery
    }

    public function test_per_line_rounding(): void
    {
        $lines = [['unit_price' => 0.335, 'quantity' => 3, 'taxable' => false]];
        $result = $this->service->compute($lines, 0, $this->tax(charge: false));

        // round(0.335 * 3, 2) = round(1.005, 2) = 1.01 (half-up via PHP round)
        $this->assertSame('1.01', $result['subtotal']);
    }
}
