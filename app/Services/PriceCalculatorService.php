<?php

namespace App\Services;

use Carbon\Carbon;

class PriceCalculatorService
{
    /**
     * Calculate the total price for a stay
     *
     * @param array $rates Array of rate periods from Bookerville
     * @param array $fees Fee structure from Bookerville
     * @param string $checkIn Check-in date (Y-m-d)
     * @param string $checkOut Check-out date (Y-m-d)
     * @param int $guests Number of guests
     * @param int $freeGuests Number of guests included in base price
     * @return array Price breakdown
     */
    public function calculateStayPrice(
        array $rates,
        array $fees,
        string $checkIn,
        string $checkOut,
        int $guests = 2,
        int $freeGuests = 2
    ): array {
        // Calculate number of nights
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = $checkInDate->diffInDays($checkOutDate);

        if ($nights <= 0) {
            return [
                'error' => 'Invalid date range',
                'nights' => 0
            ];
        }

        // Find applicable rate for the check-in date
        $applicableRate = $this->findApplicableRate($rates, $checkIn);

        if (!$applicableRate) {
            return [
                'error' => 'No applicable rate found',
                'nights' => $nights
            ];
        }

        // Get weekend nights configuration from the rate
        $weekendNightsConfig = $applicableRate['weekend_nights'] ?? 'Fri|Sat';

        // Count weekend vs weekday nights
        $weekendNights = $this->countWeekendNights($checkIn, $checkOut, $weekendNightsConfig);
        $weekdayNights = $nights - $weekendNights;

        // Get nightly and weekend rates
        $nightlyRate = (float) ($applicableRate['nightly_rate'] ?? 0);
        $weekendRate = (float) ($applicableRate['weekend_rate'] ?? $nightlyRate);

        // Calculate base price
        $basePrice = ($weekdayNights * $nightlyRate) + ($weekendNights * $weekendRate);

        // Get fees
        $cleaningFee = (float) ($fees['cleaning_fee'] ?? 0);
        $taxRate = (float) ($fees['tax_rate'] ?? 0);
        $additionalGuestFeePerNight = (float) ($fees['additional_guest_fee'] ?? 0);
        $currency = $fees['currency'] ?? 'USD';

        // Calculate additional guest fee
        $additionalGuestFee = 0;
        if ($guests > $freeGuests && $additionalGuestFeePerNight > 0) {
            $extraGuests = $guests - $freeGuests;
            $additionalGuestFee = round($extraGuests * $additionalGuestFeePerNight * $nights, 2);
        }

        // Calculate subtotal (base + cleaning + additional guest fee)
        $subtotal = round($basePrice + $cleaningFee + $additionalGuestFee, 2);

        // Calculate tax
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        // Calculate estimated Airbnb service fee (14.2% average guest fee)
        $estimatedAirbnbFee = round($subtotal * 0.142, 2);

        // Calculate estimated total
        $estimatedTotal = round($subtotal + $taxAmount + $estimatedAirbnbFee, 2);

        // Calculate nightly average
        $nightlyAvg = $nights > 0 ? round($basePrice / $nights, 2) : 0;

        return [
            'nights' => $nights,
            'weekday_nights' => $weekdayNights,
            'weekend_nights' => $weekendNights,
            'nightly_avg' => $nightlyAvg,
            'base_price' => round($basePrice, 2),
            'cleaning_fee' => round($cleaningFee, 2),
            'additional_guest_fee' => $additionalGuestFee,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'estimated_airbnb_fee' => $estimatedAirbnbFee,
            'estimated_total' => $estimatedTotal,
            'currency' => $currency,
            'rate_season' => $applicableRate['season'] ?? 'Default',
            'guests' => $guests,
            'free_guests' => $freeGuests
        ];
    }

    /**
     * Find the applicable rate for a given check-in date
     *
     * @param array $rates Array of rate periods
     * @param string $checkIn Check-in date (Y-m-d)
     * @return array|null The applicable rate or null
     */
    public function findApplicableRate(array $rates, string $checkIn): ?array
    {
        if (empty($rates)) {
            return null;
        }

        $checkInDate = Carbon::parse($checkIn);

        // Find the first rate where check-in falls within the date range
        foreach ($rates as $rate) {
            $startDate = isset($rate['start_date']) ? Carbon::parse($rate['start_date']) : null;
            $endDate = isset($rate['end_date']) ? Carbon::parse($rate['end_date']) : null;

            if ($startDate && $endDate) {
                if ($checkInDate->between($startDate, $endDate)) {
                    return $rate;
                }
            }
        }

        // Fallback to the first rate if no match found
        return $rates[0] ?? null;
    }

    /**
     * Count the number of weekend nights in a stay
     *
     * @param string $checkIn Check-in date (Y-m-d)
     * @param string $checkOut Check-out date (Y-m-d)
     * @param string $weekendNights Weekend nights configuration (e.g., 'Fri|Sat' or 'Fri|Sat|Sun')
     * @return int Number of weekend nights
     */
    public function countWeekendNights(string $checkIn, string $checkOut, string $weekendNights = 'Fri|Sat'): int
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // Parse weekend nights string to get day numbers
        // Carbon: 0=Sunday, 1=Monday, ..., 5=Friday, 6=Saturday
        $dayMap = [
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6
        ];

        $weekendDays = [];
        $parts = explode('|', strtolower($weekendNights));

        foreach ($parts as $part) {
            $part = trim($part);
            // Handle both full names and abbreviations
            foreach ($dayMap as $abbrev => $dayNum) {
                if (str_starts_with($part, $abbrev)) {
                    $weekendDays[] = $dayNum;
                    break;
                }
            }
        }

        // If no valid days parsed, default to Friday and Saturday
        if (empty($weekendDays)) {
            $weekendDays = [5, 6]; // Friday and Saturday
        }

        $count = 0;
        $currentDate = $checkInDate->copy();

        // Loop through each night of the stay (not including checkout day)
        while ($currentDate->lt($checkOutDate)) {
            if (in_array($currentDate->dayOfWeek, $weekendDays)) {
                $count++;
            }
            $currentDate->addDay();
        }

        return $count;
    }
}
