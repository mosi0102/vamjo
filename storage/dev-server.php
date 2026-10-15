<?php

/**
 * روتر محلی برای اجرای پروژه با وب‌سرور داخلی PHP (بدون artisan serve).
 *
 *   php -d extension=mbstring -d extension=fileinfo -S 127.0.0.1:8123 -t public storage/dev-server.php
 *
 * دلیل وجود: در این ماشین افزونه‌های mbstring/fileinfo در php.ini فعال نیستند، پس
 * `php artisan serve` (که پرچم‌های -d را به فرزند منتقل نمی‌کند) خطا می‌دهد.
 */
$public = dirname(__DIR__).'/public';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri !== '/' && is_file($public.$uri)) {
    return false;   // فایل‌های استاتیک را خود وب‌سرور برگرداند
}

require_once $public.'/index.php';
