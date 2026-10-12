<?php

/*
 | منوی پنل (کاربر / ادمین / پشتیبان) – هر آیتم با roles مشخص می‌شود.
 | active: الگوهای request()->is(...) | bottom: در نویگیشن پایین موبایل بیاید (۴ مورد اول هر نقش)
 | آیتم‌های adminPanel/support نمونه‌اند و با طراحی پنل مدیریت تکمیل می‌شوند.
 */
return [
    'roles' => ['user' => 'کاربر', 'adminPanel' => 'مدیر سیستم', 'support' => 'پشتیبان'],

    'menu' => [
        // ----- کاربر -----
        ['label' => 'داشبورد',          'icon' => 'dashboard', 'url' => '/userPanel',            'active' => ['userPanel'],            'roles' => ['user'], 'bottom' => true],
        ['label' => 'آگهی های من',      'icon' => 'book',      'url' => '/userPanel/ads',        'active' => ['userPanel/ads*'],       'roles' => ['user'], 'bottom' => true],
        ['label' => 'درخواست های من',   'icon' => 'doc',       'url' => '/userPanel/requests',   'active' => ['userPanel/requests*'],  'roles' => ['user'], 'bottom' => true],
        ['label' => 'پرداخت های من',    'icon' => 'card',      'url' => '/userPanel/payments',   'active' => ['userPanel/payments*'],  'roles' => ['user'], 'bottom' => true],
        ['label' => 'آگهی های برگزیده', 'icon' => 'bookmark',  'url' => '/userPanel/favorites',  'active' => ['userPanel/favorites*'], 'roles' => ['user']],
        ['label' => 'رادار وام',        'icon' => 'radar',     'url' => '/userPanel/radar',      'active' => ['userPanel/radar*'],     'roles' => ['user']],
        ['label' => 'تیکت های پشتیبانی','icon' => 'chat',      'url' => '/userPanel/tickets',    'active' => ['userPanel/tickets*'],   'roles' => ['user']],
        ['label' => 'اطلاعات هویتی',    'icon' => 'idcard',    'url' => '/userPanel/verify',     'active' => ['userPanel/verify*'],    'roles' => ['user']],


        // ----- مدیر / پشتیبان -----   (badge = تعداد موارد منتظر اقدام؛ از کنترلر/View Composer مقدار بدهید)
        ['label' => 'داشبورد',          'icon' => 'dashboard', 'url' => '/adminPanel',               'active' => ['adminPanel'],                'roles' => ['adminPanel', 'support'], 'bottom' => true],
        ['label' => 'کاربران',          'icon' => 'users',     'url' => '/adminPanel/users',         'active' => ['adminPanel/users*'],         'roles' => ['adminPanel', 'support']],
        ['label' => 'احراز هویت ها',    'icon' => 'shield',    'url' => '/adminPanel/verifications', 'active' => ['adminPanel/verifications*'], 'roles' => ['adminPanel', 'support'], 'bottom' => true, 'badge' => 7],
        ['label' => 'آگهی ها',          'icon' => 'book',      'url' => '/adminPanel/ads',           'active' => ['adminPanel/ads*'],           'roles' => ['adminPanel', 'support'], 'bottom' => true, 'badge' => 5],
        ['label' => 'امور مالی',        'icon' => 'card',      'url' => '/adminPanel/finance',       'active' => ['adminPanel/finance*'],       'roles' => ['adminPanel']],
        ['label' => 'تیکت های پشتیبانی','icon' => 'chat',      'url' => '/adminPanel/tickets',       'active' => ['adminPanel/tickets*'],       'roles' => ['adminPanel', 'support'], 'bottom' => true, 'badge' => 4],
    ],
];
