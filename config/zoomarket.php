<?php

return [

    /*
    | The regions (viloyatlar) a listing's location is chosen from. The exact place inside the region
    | (city, district) is typed in the listing's "location" field.
    */
    'regions' => [
        'Andijon viloyati',
        'Buxoro viloyati',
        'Jizzax viloyati',
        'Qashqadaryo viloyati',
        'Navoiy viloyati',
        'Namangan viloyati',
        'Samarqand viloyati',
        'Surxondaryo viloyati',
        'Sirdaryo viloyati',
        'Toshkent viloyati',
        'Farg\'ona viloyati',
        'Xorazm viloyati',
        // Not in the twelve above, but listings come from them: Toshkent city is its own unit, and so is Karakalpakstan
        'Toshkent shahri',
        'Qoraqalpog\'iston Respublikasi',
    ],

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
