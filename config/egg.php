<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ração — regra de conversão saco → valor
    |--------------------------------------------------------------------------
    | Ex.: 1 saco de 50 kg = 1800 MT  →  preço/kg = 1800 / 50 = 36 MT/kg
    */
    'feed_bag_kg' => (float) env('EGG_FEED_BAG_KG', 50),
    'feed_bag_price_mzn' => (float) env('EGG_FEED_BAG_PRICE_MZN', 1800),
];
