<?php

namespace App\Services;

/**
 * The money kernel. Pure and exhaustively unit-tested (docs 11). Never trusts
 * client-sent totals. Tax applies only to taxable lines; a discount reduces the
 * taxable base proportionally; tax-included mode extracts tax without changing the
 * total. See docs 03 §2–3.
 */
class OrderTotalsService
{
    /**
     * @param  list<array{unit_price: float|string, quantity: int, taxable: bool}>  $lines
     * @param  array{charge_sales_tax: bool, tax_rate: float|string, tax_included_in_price: bool}  $tax
     * @return array{subtotal: string, tax_amount: string, total: string, tax_rate: string}
     */
    public function compute(array $lines, float $discount, array $tax, float $deliveryFee = 0.0): array
    {
        $subtotal = 0.0;
        $taxableBase = 0.0;

        foreach ($lines as $line) {
            $lineTotal = round((float) $line['unit_price'] * (int) $line['quantity'], 2);
            $subtotal += $lineTotal;
            if ($line['taxable']) {
                $taxableBase += $lineTotal;
            }
        }

        $subtotal = round($subtotal, 2);
        $discount = round(min(max($discount, 0.0), $subtotal), 2);
        $rate = (float) $tax['tax_rate'] / 100;

        // Discount shrinks the taxable base proportionally.
        $proportion = $subtotal > 0 ? ($subtotal - $discount) / $subtotal : 0.0;
        $taxableAfterDiscount = round($taxableBase * $proportion, 2);

        $taxAmount = 0.0;
        if ($tax['charge_sales_tax'] && $rate > 0) {
            $taxAmount = $tax['tax_included_in_price']
                ? round($taxableAfterDiscount - ($taxableAfterDiscount / (1 + $rate)), 2)  // extract
                : round($taxableAfterDiscount * $rate, 2);                                  // add
        }

        $total = $tax['tax_included_in_price']
            ? round($subtotal - $discount + $deliveryFee, 2)               // tax already inside prices
            : round($subtotal - $discount + $taxAmount + $deliveryFee, 2);

        return [
            'subtotal' => $this->money($subtotal),
            'tax_amount' => $this->money($taxAmount),
            'total' => $this->money($total),
            'tax_rate' => $this->money((float) $tax['tax_rate']),
        ];
    }

    private function money(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }
}
