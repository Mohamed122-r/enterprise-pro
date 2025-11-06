<?php
// التحقق من أن Laravel مثبت
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    die('❌ Composer dependencies not installed!');
}

require_once __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);
