<?php
// Forward semua request dari Vercel Serverless Function ke front-controller CodeIgniter 4

// Pemetaan env dari Vercel (karena Vercel tidak mendukung karakter titik)
if (!empty(getenv('DB_HOSTNAME'))) {
    $_ENV['database.default.hostname'] = getenv('DB_HOSTNAME');
    $_SERVER['database.default.hostname'] = getenv('DB_HOSTNAME');
}
if (!empty(getenv('DB_DATABASE'))) {
    $_ENV['database.default.database'] = getenv('DB_DATABASE');
    $_SERVER['database.default.database'] = getenv('DB_DATABASE');
}
if (!empty(getenv('DB_USERNAME'))) {
    $_ENV['database.default.username'] = getenv('DB_USERNAME');
    $_SERVER['database.default.username'] = getenv('DB_USERNAME');
}
if (!empty(getenv('DB_PASSWORD'))) {
    $_ENV['database.default.password'] = getenv('DB_PASSWORD');
    $_SERVER['database.default.password'] = getenv('DB_PASSWORD');
}
if (!empty(getenv('DB_PORT'))) {
    $_ENV['database.default.port'] = getenv('DB_PORT');
    $_SERVER['database.default.port'] = getenv('DB_PORT');
}
if (!empty(getenv('ENCRYPTION_KEY'))) {
    $_ENV['encryption.key'] = getenv('ENCRYPTION_KEY');
    $_SERVER['encryption.key'] = getenv('ENCRYPTION_KEY');
}

// Pastikan baseURL mengarah ke domain host produksi (polara-flame.vercel.app)
$targetHost = getenv('APP_BASEURL') ?: ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'polara-flame.vercel.app');
// Jika domain preview yang dihasilkan vercel terdeteksi, normalisasi ke domain utama proyek
if (strpos($targetHost, 'polara-') === 0 && strpos($targetHost, '.vercel.app') !== false && strpos($targetHost, 'polara-flame.vercel.app') === false) {
    $targetHost = 'polara-flame.vercel.app';
}
$targetHost = preg_replace('#^https?://#', '', rtrim($targetHost, '/'));
$currentBaseUrl = 'https://' . $targetHost . '/';

$_ENV['app.baseURL'] = $currentBaseUrl;
$_SERVER['app.baseURL'] = $currentBaseUrl;
putenv("app.baseURL={$currentBaseUrl}");

$_ENV['app.indexPage'] = '';
$_SERVER['app.indexPage'] = '';
putenv("app.indexPage=");



require __DIR__ . '/../public/index.php';

