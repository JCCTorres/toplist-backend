<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Populate airbnb_id on the properties table by matching Bookerville property addresses
 * with Airbnb listing data (from "Airbnb Listings Data (3).xlsx" / client_properties).
 *
 * Matching was done by house number (first number in the address).
 * Airbnb IDs were extracted from airbnbUrl fields to avoid JavaScript number precision loss.
 *
 * To update an existing property's Airbnb ID or add a new one in the future,
 * use the Filament admin panel: Properties > Edit > "Airbnb Integration" section.
 */
return new class extends Migration
{
    /**
     * Bookerville property_id => Airbnb listing ID (from URL, not JSON number)
     *
     * Matched by house number in address:
     *   Bookerville address house# == Airbnb address house#
     */
    private array $mapping = [
        // property_id => airbnb_id (string to preserve precision for large IDs)
        '11684' => '857979998250100076',   // 1001 Baseball And Boardwalk Ct
        '10705' => '53159063',             // 3048 Cypress Gardens Ct
        '8558'  => '37538709',             // 1020 Baseball And Boardwalk Ct
        '11349' => '746746804361468563',   // 3050 Cypress Gardens Ct
        '11773' => '929745801403145100',   // 2130 Water Mania Ct
        '8372'  => '23063486',             // 3155 Wet N Wild Ct
        '9629'  => '42763626',             // 3111 Magic Kingdom Ct
        '9630'  => '43653417',             // 1120 Spaceport Ct
        '9627'  => '44125167',             // 3157 Wet N Wild Ct
        '11820' => '987377527010705358',   // 2116 Water Mania Ct
        '10706' => '54060170',             // 3178 Wet N Wild Ct
        '6073'  => '1028741013051744249',  // 3171 Wet N Wild Ct
        '11362' => '754743309149746143',   // 1114 Spaceport Ct
        '6582'  => '20483092',             // 2017 Disney MGM Studios Ct
        '10886' => '566352978010101798',   // 3194 Sea World Ct
        '10887' => '691695025778461811',   // 3145 Wet N Wild Ct
        '3929'  => '8608361',              // 3077 Rosie O Grady Ct
        '3594'  => '6629005',              // 3025 Universal Studios Ct
        '11685' => '858024866311666457',   // 3135 Magic Kingdom Ct
        '9631'  => '43834746',             // 1108 Spaceport Ct
        '3799'  => '7817121',              // 3200 Sea World Ct
        '8155'  => '32409750',             // 3148 Wet N Wild Ct
        '9628'  => '42661403',             // 3142 Wet N Wild Ct
        '10704' => '53158477',             // 3184 Sea World Ct
        '9929'  => '44125403',             // 3063 Cypress Gardens Ct
        '8499'  => '6629060',              // 3212 Sea World Ct
        '3032'  => '2206869',              // 3016 Bonfire Beach Dr
        '10369' => '48475974',             // 1556 Carey Palm Cir
        '9451'  => '40570414',             // 129 Madiera Beach Blvd
        '11350' => '765538654001747470',   // 157 Hideaway Beach Ln
        '2794'  => '4355718',              // 946 Park Terrace Circle
        '11686' => '859665505919638004',   // 172 Hideaway Beach Ln
        '11321' => '41366819',             // 4679 Golden Beach Ct
        '7624'  => '48476005',             // 115 Madiera Beach Blvd
    ];

    /**
     * Unmatched Bookerville properties (no corresponding Airbnb listing):
     *   3094  - 3179 Wet N Wild Court (no Airbnb listing with house# 3179)
     *   11844 - 3181 Wet N Wild Court (no Airbnb listing with house# 3181)
     *   11908 - 110 Madiera Beach Blvd (no Airbnb listing with house# 110)
     *   3031  - 911 Park Terrace Circle (no Airbnb listing with house# 911)
     *   11562 - 1001 Fanny St (house# 1001 exists but different street)
     *   5572  - 943 Lake Berkley Drive (no Airbnb listing with house# 943)
     */

    public function up(): void
    {
        foreach ($this->mapping as $propertyId => $airbnbId) {
            DB::table('properties')
                ->where('property_id', $propertyId)
                ->update(['airbnb_id' => $airbnbId]);
        }
    }

    public function down(): void
    {
        $propertyIds = array_keys($this->mapping);

        DB::table('properties')
            ->whereIn('property_id', $propertyIds)
            ->update(['airbnb_id' => null]);
    }
};
