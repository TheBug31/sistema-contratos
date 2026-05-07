<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Apunta al proyecto Laravel que está un nivel arriba de htdocs
require __DIR__.'/laravel/vendor/autoload.php';

$app = require_once __DIR__.'/laravel/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);