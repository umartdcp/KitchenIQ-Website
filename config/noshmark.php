<?php

return [
    'serving_sizes' => [2, 4, 8, 12],
    'pateela_persons' => 30,
    'separate_packing_per_person' => 650,
    'pateela_price' => 18000,
    'delivery_fee' => 120,
    'lead_days' => 2,
    'max_quantity' => 20,
    'max_deliveries' => 24,

    'schedules' => [
        'weekly' => [
            'label' => 'Weekly',
            'description' => 'A fresh, curated menu every week.',
            'image' => 'images/menu/biryani.jpg',
            'summary' => 'Repeats every week on your selected days.',
        ],
        'every_15_days' => [
            'label' => 'Every 15 Days',
            'description' => 'Get a new menu twice a month (every 15 days).',
            'image' => 'images/menu/karahi.jpg',
            'summary' => 'Repeats every 15 days from each selected date.',
        ],
        'monthly' => [
            'label' => 'Monthly',
            'description' => 'A new, chef-curated menu every month.',
            'image' => 'images/menu/kebab.jpg',
            'summary' => 'Repeats on the same dates each month.',
        ],
    ],

    'packing' => [
        'separate' => [
            'label' => 'Separate Packing',
            'description' => 'Individually packed meals for each person.',
            'note' => 'Ideal for events, offices & individual serving.',
            'image' => 'images/menu/kebab.jpg',
        ],
        'pateela' => [
            'label' => 'Whole Pateela',
            'description' => 'A large traditional pateela for group serving.',
            'note' => 'Serves 30 persons. Perfect for family & large gatherings.',
            'image' => 'images/menu/biryani.jpg',
        ],
    ],

    'per_person_schedules' => [
        'same' => 'Same menu for everyone',
        'personalized' => 'Personalized menu per person',
    ],

    'payment_methods' => [
        'visa' => 'Visa',
        'mastercard' => 'Mastercard',
        'easypaisa' => 'Easypaisa',
        'jazzcash' => 'JazzCash',
        'bank' => 'Bank Transfer',
    ],
];
