<?php
$dirs = [
    'laravel/storage',
    'laravel/storage/framework',
    'laravel/storage/framework/sessions',
    'laravel/storage/framework/views',
    'laravel/storage/framework/cache',
    'laravel/storage/logs',
    'laravel/bootstrap/cache',
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        chmod($path, 0755);
        echo "OK: $dir\n";
    } else {
        echo "NO EXISTE: $dir\n";
    }
}

echo "\nVerificando DB...\n";
try {
    $pdo = new PDO(
        "mysql:host=sql302.infinityfree.com;port=3306;dbname=if0_41174428_contratos",
        "if0_41174428",
        "UbEXc9OMLFa5"
    );
    echo "DB: CONECTADO\n";
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
