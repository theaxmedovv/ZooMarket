<?php

/*
| What the "Yangi e'lon" form asks for, per category (keyed by the category's name, see CategorySeeder).
|
| A profile may change the labels of the basic fields and switch some of them off:
|   breed_label / breed_placeholder   the "Turi" field
|   quantity_label                    "Jami hayvonlar soni"
|   gender (bool)                     ask "Jinsi" (off: everything is counted as one group)
|   age (bool), age_label, age_placeholder
|   color (bool)
| and lists extra "fields" for that kind of animal, stored in the listing's `details` column. Field types:
|   select  options => [...], required => bool
|   number  unit => 'kg', min, max, required
|   text    max
| A category without its own entry gets the "default" profile.
*/

$yesNo = ['Ha', "Yo'q"];
$hasNot = ['Bor', "Yo'q"];

return [

    'default' => [
        'breed_label' => 'Turi',
        'breed_placeholder' => 'Masalan: Shotland, Nemis ovcharkasi, Kane-korso',
        'quantity_label' => 'Jami hayvonlar soni',
        'gender' => true,
        'age' => true,
        'age_label' => 'Yoshi',
        'age_placeholder' => 'Masalan: 3 oy, 1.5 yosh, 6 haftalik',
        'color' => true,
        'fields' => [],
    ],

    'profiles' => [

        'It' => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Nemis ovcharkasi, Labrador, Kane-korso',
            'fields' => [
                'vaccinated' => ['label' => 'Emlangan', 'type' => 'select', 'options' => $yesNo, 'required' => true],
                'passport' => ['label' => 'Veterinariya pasporti', 'type' => 'select', 'options' => $hasNot],
                'pedigree' => ['label' => 'Nasabnomasi (ota-ona hujjati)', 'type' => 'select', 'options' => $hasNot],
                'trained' => ['label' => "O'rgatilgan buyruqlar", 'type' => 'text', 'max' => 120],
            ],
        ],

        'Mushuk' => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Britan, Shotland, Sfinks, Maine-kun',
            'fields' => [
                'vaccinated' => ['label' => 'Emlangan', 'type' => 'select', 'options' => $yesNo, 'required' => true],
                'litter_trained' => ['label' => "Tuvakka o'rgangan", 'type' => 'select', 'options' => $yesNo],
                'neutered' => ['label' => 'Sterilizatsiya qilingan', 'type' => 'select', 'options' => $yesNo],
                'pedigree' => ['label' => 'Nasabnomasi', 'type' => 'select', 'options' => $hasNot],
            ],
        ],

        'Kemiruvchilar' => [
            'breed_label' => 'Turi',
            'breed_placeholder' => "Masalan: Xomyak, Dengiz cho'chqasi, Shinshilla, Kalamush",
            'quantity_label' => 'Jami hayvonlar soni',
            'fields' => [
                'cage' => ['label' => 'Katak bilan birga sotiladimi', 'type' => 'select', 'options' => $yesNo],
                'tame' => ['label' => "Qo'lga o'rgangan", 'type' => 'select', 'options' => $yesNo],
            ],
        ],

        'Quyon' => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Kaliforniya, Flandr, Dekorativ, Rex',
            'fields' => [
                'purpose' => ['label' => "Yo'nalishi", 'type' => 'select', 'options' => ['Uy hayvoni', "Go'sht", "Mo'yna", 'Nasl uchun'], 'required' => true],
                'weight_kg' => ['label' => "Og'irligi", 'type' => 'number', 'unit' => 'kg', 'min' => 0.1, 'max' => 20],
                'vaccinated' => ['label' => 'Emlangan', 'type' => 'select', 'options' => $yesNo],
            ],
        ],

        'Qushlar' => [
            'breed_label' => 'Turi',
            'breed_placeholder' => "Masalan: Kanareyka, To'ti, Sa'va, Bulbul",
            'quantity_label' => 'Jami qushlar soni',
            'fields' => [
                'cage' => ['label' => 'Katak bilan birga sotiladimi', 'type' => 'select', 'options' => $yesNo],
                'tame' => ['label' => "Qo'lga o'rgangan", 'type' => 'select', 'options' => $yesNo],
                'sings' => ['label' => 'Sayraydi yoki gapiradi', 'type' => 'select', 'options' => $yesNo],
            ],
        ],

        'Kaptar' => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Nikolayev, Bakinskiy, Dekorativ',
            'quantity_label' => 'Jami kaptarlar soni',
            'fields' => [
                'flight' => ['label' => 'Parvoz turi', 'type' => 'select', 'options' => ['Baland uchadigan', "O'ynaydigan", 'Sport (pochta)', 'Dekorativ'], 'required' => true],
                'ring' => ['label' => 'Halqasi bor', 'type' => 'select', 'options' => $yesNo],
            ],
        ],

        'Parrandalar' => [
            'breed_label' => 'Turi va zoti',
            'breed_placeholder' => "Masalan: Tovuq (Brama), Xo'roz, O'rdak, G'oz, Kurka, Bedana",
            'quantity_label' => 'Jami parrandalar soni',
            'fields' => [
                'purpose' => ['label' => "Yo'nalishi", 'type' => 'select', 'options' => ['Tuxum', "Go'sht", "Tuxum-go'sht", 'Bezak'], 'required' => true],
                'weight_kg' => ['label' => "O'rtacha og'irligi", 'type' => 'number', 'unit' => 'kg', 'min' => 0.05, 'max' => 30],
                'vaccinated' => ['label' => 'Emlangan', 'type' => 'select', 'options' => $yesNo],
            ],
        ],

        'Baliq' => [
            'breed_label' => 'Turi',
            'breed_placeholder' => 'Masalan: Guppi, Skalyariya, Diskus, Karp',
            'quantity_label' => 'Jami baliqlar soni',
            'gender' => false,
            'age' => false,
            'fields' => [
                'water' => ['label' => 'Suv turi', 'type' => 'select', 'options' => ['Chuchuk', "Sho'r"], 'required' => true],
                'size_cm' => ['label' => 'Kattaligi', 'type' => 'number', 'unit' => 'sm', 'min' => 0.5, 'max' => 200],
                'aquarium_l' => ['label' => 'Mos akvarium hajmi', 'type' => 'number', 'unit' => 'litr', 'min' => 1, 'max' => 100000],
                'temperament' => ['label' => 'Xulqi', 'type' => 'select', 'options' => ['Tinch', 'Boshqalar bilan yaxshi chiqishadi', 'Yirtqich']],
            ],
        ],

        'Sudralib yuruvchilar' => [
            'breed_label' => 'Turi',
            'breed_placeholder' => 'Masalan: Gekkon, Toshbaqa, Ilon, Agama',
            'fields' => [
                'venomous' => ['label' => 'Zaharlimi', 'type' => 'select', 'options' => ["Yo'q", 'Ha'], 'required' => true],
                'terrarium' => ['label' => 'Terrarium bilan birga', 'type' => 'select', 'options' => $yesNo],
                'origin' => ['label' => 'Kelib chiqishi', 'type' => 'select', 'options' => ['Asirlikda ko\'paytirilgan', 'Tabiatdan']],
                'documents' => ['label' => 'Hujjati', 'type' => 'select', 'options' => $hasNot],
            ],
        ],

        'Ekzotik hayvonlar' => [
            'breed_label' => 'Turi',
            'breed_placeholder' => 'Masalan: Fenek, Servol, Kapibara, Mayda maymun',
            'fields' => [
                'permit' => ['label' => 'Rasmiy ruxsatnoma (CITES)', 'type' => 'select', 'options' => $hasNot, 'required' => true],
                'origin' => ['label' => 'Kelib chiqishi', 'type' => 'select', 'options' => ['Asirlikda ko\'paytirilgan', 'Tabiatdan']],
                'care' => ['label' => 'Parvarish murakkabligi', 'type' => 'select', 'options' => ['Oson', "O'rtacha", 'Murakkab'], 'required' => true],
            ],
        ],

        'Qoramol' => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Golshtin, Simmental, Qora-ola, Hereford',
            'quantity_label' => 'Jami bosh soni',
            'age_placeholder' => 'Masalan: 8 oy, 2 yosh, 4 yosh',
            'fields' => [
                'weight_kg' => ['label' => "Og'irligi", 'type' => 'number', 'unit' => 'kg', 'min' => 10, 'max' => 2000, 'required' => true],
                'purpose' => ['label' => "Yo'nalishi", 'type' => 'select', 'options' => ['Sut', "Go'sht", 'Nasl uchun', "Qo'shma"], 'required' => true],
                'milk_l' => ['label' => 'Kunlik sut', 'type' => 'number', 'unit' => 'litr', 'min' => 0, 'max' => 100],
                'pregnant' => ['label' => "Bo'g'ozligi", 'type' => 'select', 'options' => ["Yo'q", "Bo'g'oz"]],
                'vet_certificate' => ['label' => 'Veterinariya guvohnomasi', 'type' => 'select', 'options' => $hasNot],
            ],
        ],

        "Qo'y va echki" => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Hisori, Jaydari, Qorako\'l, Saanen',
            'quantity_label' => 'Jami bosh soni',
            'age_placeholder' => 'Masalan: 6 oy, 1 yosh, 3 yosh',
            'fields' => [
                'weight_kg' => ['label' => "Og'irligi", 'type' => 'number', 'unit' => 'kg', 'min' => 1, 'max' => 300, 'required' => true],
                'purpose' => ['label' => "Yo'nalishi", 'type' => 'select', 'options' => ["Go'sht", 'Sut', 'Jun', 'Nasl uchun'], 'required' => true],
                'pregnant' => ['label' => "Bo'g'ozligi", 'type' => 'select', 'options' => ["Yo'q", "Bo'g'oz"]],
                'vet_certificate' => ['label' => 'Veterinariya guvohnomasi', 'type' => 'select', 'options' => $hasNot],
            ],
        ],

        'Otlar' => [
            'breed_label' => 'Zoti',
            'breed_placeholder' => 'Masalan: Qorabayir, Arab, Axaltekin, Kokpar oti',
            'quantity_label' => 'Jami otlar soni',
            'age_placeholder' => 'Masalan: 2 yosh, 5 yosh, 8 yosh',
            'fields' => [
                'height_cm' => ['label' => "Bo'yi", 'type' => 'number', 'unit' => 'sm', 'min' => 50, 'max' => 220, 'required' => true],
                'purpose' => ['label' => 'Maqsadi', 'type' => 'select', 'options' => ['Minish va sport', 'Poyga', 'Ish (arava, dala)', 'Nasl uchun'], 'required' => true],
                'trained' => ['label' => "O'rgatilganligi", 'type' => 'select', 'options' => ["O'rgatilmagan", 'Egar ostida', 'Aravaga']],
                'vet_certificate' => ['label' => 'Veterinariya guvohnomasi', 'type' => 'select', 'options' => $hasNot],
            ],
        ],

        'Tuya' => [
            'breed_label' => 'Turi',
            'breed_placeholder' => "Masalan: Bir o'rkachli, Ikki o'rkachli",
            'quantity_label' => 'Jami bosh soni',
            'age_placeholder' => 'Masalan: 1 yosh, 4 yosh',
            'fields' => [
                'weight_kg' => ['label' => "Og'irligi", 'type' => 'number', 'unit' => 'kg', 'min' => 50, 'max' => 1000],
                'purpose' => ['label' => "Yo'nalishi", 'type' => 'select', 'options' => ['Sut', "Go'sht", 'Minish va ish', 'Nasl uchun'], 'required' => true],
                'vet_certificate' => ['label' => 'Veterinariya guvohnomasi', 'type' => 'select', 'options' => $hasNot],
            ],
        ],

        'Asalarichilik' => [
            'breed_label' => 'Ari zoti',
            'breed_placeholder' => 'Masalan: Karnika, Karpat, Rus ari zoti',
            'quantity_label' => 'Soni',
            'gender' => false,
            'age' => false,
            'color' => false,
            'fields' => [
                'product' => ['label' => 'Nima sotilmoqda', 'type' => 'select', 'options' => ['Ari oilasi', 'Ari uyasi (qutisi)', 'Asal', 'Jihozlar'], 'required' => true],
                'hive_type' => ['label' => 'Uya turi', 'type' => 'select', 'options' => ['Langstrot', 'Dadan', 'Ruta', 'Boshqa']],
                'honey_kg' => ['label' => 'Asal miqdori', 'type' => 'number', 'unit' => 'kg', 'min' => 0, 'max' => 100000],
            ],
        ],

        'Boshqa' => [],
    ],
];
