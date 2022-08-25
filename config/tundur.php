<?php

return [

    'apple_password' => env('APPLE_PASSWORD', "4632c3eaff084192a3f53ec1573b8b7c"),

    'subscription_plans' => [
        '1'  => [
            'product_id' => '',
            'plan_name' => 'Tundur Free',
            'features' => [
                'available_likes' => 10,
                'available_super_likes' => 1,
                'available_last_likes' => 0,
                'available_boost' => 0,
                'available_top_picked' => 0,
            ]
        ],
        '2'  => [
            'product_id' => '',
            'plan_name' => 'Tundur Plus'
        ],
        '3'  => [
            'product_id' => '',
            'plan_name' => 'Tundur gold plan 1 month'
        ],
    ],


    'FIREBASE_SERVER_KEY' => env('FIREBASE_SERVER_KEY', 'AAAAhETRNSo:APA91bFgnyfPISg3_vbpNjbOesdLJ3zHJgCxwRpZ-OINXaJvwtGj-JlarAtSm3_yp3AN88m7ppSJi9Ny_ZkNGwA9LonDdmfqtY19A6PgP3xvp3J3D87MSNpJJk2WaiE6xpdmM_6dDa14'),

    'APPLE_VERIFY_RECEIPT_URL' => 'https://sandbox.itunes.apple.com/verifyReceipt',

    'ANDROID_VERIFY_RECEIPT_URL' => ''
];
