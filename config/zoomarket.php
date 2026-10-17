<?php

return [

    /*
    | How many so'm one unit of each listing currency is worth. Only used to compare
    | prices across currencies ("Eng arzon narxlarda", price sorting), never shown.
    | Update them in .env as the rates move.
    */
    'currency_rates' => [
        'UZS' => 1,
        'USD' => (float) env('RATE_USD', 12700),
        'EUR' => (float) env('RATE_EUR', 14700),
        'RUB' => (float) env('RATE_RUB', 160),
    ],

];
