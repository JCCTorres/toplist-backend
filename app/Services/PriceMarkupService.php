<?php

namespace App\Services;

class PriceMarkupService
{
    /**
     * Apply configured markup to a single price.
     *
     * @param float|int $price
     * @return float
     */
    public static function apply($price): float
    {
        if (!is_numeric($price) || $price <= 0) {
            return (float) $price;
        }

        $percent = config('app.price_markup_percent', 25);

        return round($price * (1 + $percent / 100), 2);
    }

    /**
     * Apply markup to every monetary line-item in a price breakdown array,
     * then recompute subtotal and total from the marked-up components.
     *
     * @param array $breakdown
     * @return array
     */
    public static function applyToBreakdown(array $breakdown): array
    {
        // Mark up the individual line items
        $lineItems = [
            'nightly_avg',
            'base_price',
            'cleaning_fee',
            'additional_guest_fee',
        ];

        foreach ($lineItems as $key) {
            if (isset($breakdown[$key]) && is_numeric($breakdown[$key]) && $breakdown[$key] > 0) {
                $breakdown[$key] = self::apply($breakdown[$key]);
            }
        }

        // Recompute subtotal from marked-up components
        $breakdown['subtotal'] = round(
            ($breakdown['base_price'] ?? 0)
            + ($breakdown['cleaning_fee'] ?? 0)
            + ($breakdown['additional_guest_fee'] ?? 0),
            2
        );

        // Recompute tax amount from new subtotal
        $taxRate = $breakdown['tax_rate'] ?? 0;
        $breakdown['tax_amount'] = round($breakdown['subtotal'] * ($taxRate / 100), 2);

        // Recompute estimated airbnb fee (taxes) from new subtotal at 13.5%
        $breakdown['estimated_airbnb_fee'] = round($breakdown['subtotal'] * 0.135, 2);

        // Recompute total
        $breakdown['estimated_total'] = round(
            $breakdown['subtotal']
            + $breakdown['tax_amount']
            + $breakdown['estimated_airbnb_fee'],
            2
        );

        return $breakdown;
    }

    /**
     * Apply markup to rate period amounts (nightly_rate, weekend_rate).
     *
     * @param array $rates
     * @return array
     */
    public static function applyToRates(array $rates): array
    {
        foreach ($rates as &$rate) {
            if (isset($rate['nightly_rate']) && is_numeric($rate['nightly_rate'])) {
                $rate['nightly_rate'] = self::apply($rate['nightly_rate']);
            }
            if (isset($rate['weekend_rate']) && is_numeric($rate['weekend_rate'])) {
                $rate['weekend_rate'] = self::apply($rate['weekend_rate']);
            }
        }
        unset($rate);

        return $rates;
    }
}
