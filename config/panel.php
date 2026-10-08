<?php

/*
 | منوی پنل (کاربر / ادمین / پشتیبان) – هر آیتم با roles مشخص می‌شود.
 | active: الگوهای request()->is(...) | bottom: در نویگیشن پایین موبایل بیاید (۴ مورد اول هر نقش)
 | آیتم‌های admin/support نمونه‌اند و با طراحی پنل مدیریت تکمیل می‌شوند.
 */
return [
    'roles' => ['user' => 'کاربر', 'admin' => 'مدیر سیستم', 'support' => 'پشتیبان'],

    'menu' => [
        // ----- کاربر -----
        ['label' => 'داشبورد',          'icon' => 'dashboard', 'url' => '/panel',            'active' => ['panel'],            'roles' => ['user'], 'bottom' => true],
        ['label' => 'آگهی های من',      'icon' => 'book',      'url' => '/panel/ads',        'active' => ['panel/ads*'],       'roles' => ['user'], 'bottom' => true],
        ['label' => 'درخواست های من',   'icon' => 'doc',       'url' => '/panel/requests',   'active' => ['panel/requests*'],  'roles' => ['user'], 'bottom' => true],
        ['label' => 'پرداخت های من',    'icon' => 'card',      'url' => '/panel/payments',   'active' => ['panel/payments*'],  'roles' => ['user'], 'bottom' => true],
        ['label' => 'آگهی های برگزیده', 'icon' => 'bookmark',  'url' => '/panel/favorites',  'active' => ['panel/favorites*'], 'roles' => ['user']],
        ['label' => 'رادار وام',        'icon' => 'radar',     'url' => '/panel/radar',      'active' => ['panel/radar*'],     'roles' => ['user']],
        ['label' => 'تیکت های پشتیبانی','icon' => 'chat',      'url' => '/panel/tickets',    'active' => ['panel/tickets*'],   'roles' => ['user']],
        ['label' => 'اطلاعات هویتی',    'icon' => 'idcard',    'url' => '/panel/verify',     'active' => ['panel/verify*'],    'roles' => ['user']],

        // ----- مدیر / پشتیبان (نمونه) -----
        ['label' => 'داشبورد مدیریت',   'icon' => 'dashboard', 'url' => '/admin',               'active' => ['admin'],                'roles' => ['admin', 'support'], 'bottom' => true],
        ['label' => 'کاربران',          'icon' => 'users',     'url' => '/admin/users',         'active' => ['admin/users*'],         'roles' => ['admin'],            'bottom' => true],
        ['label' => 'احراز هویت ها',    'icon' => 'shield',    'url' => '/admin/verifications', 'active' => ['admin/verifications*'], 'roles' => ['admin', 'support'], 'bottom' => true],
        ['label' => 'آگهی ها',          'icon' => 'book',      'url' => '/admin/ads',           'active' => ['admin/ads*'],           'roles' => ['admin'],            'bottom' => true],
        ['label' => 'درخواست ها',       'icon' => 'doc',       'url' => '/admin/requests',      'active' => ['admin/requests*'],      'roles' => ['admin']],
        ['label' => 'پرداخت ها',        'icon' => 'card',      'url' => '/admin/payments',      'active' => ['admin/payments*'],      'roles' => ['admin']],
        ['label' => 'تیکت های پشتیبانی','icon' => 'chat',      'url' => '/admin/tickets',       'active' => ['admin/tickets*'],       'roles' => ['admin', 'support'], 'bottom' => true],
        ['label' => 'تنظیمات',          'icon' => 'settings',  'url' => '/admin/settings',      'active' => ['admin/settings*'],      'roles' => ['admin']],
    ],
];
