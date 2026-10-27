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
// Wajib SSL / TLS untuk TiDB Cloud Serverless (harus berupa array agar mengaktifkan MYSQLI_CLIENT_SSL)
$_ENV['database.default.encrypt'] = ['ssl_verify' => false];
$_SERVER['database.default.encrypt'] = ['ssl_verify' => false];


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



// Set temporary writable paths before boot
$tmpWritable = '/tmp/writable';
if (!is_dir($tmpWritable)) {
    @mkdir($tmpWritable . '/cache', 0777, true);
    @mkdir($tmpWritable . '/logs', 0777, true);
    @mkdir($tmpWritable . '/session', 0777, true);
    @mkdir($tmpWritable . '/uploads', 0777, true);
}
$_ENV['paths.writableDirectory'] = $tmpWritable;
$_SERVER['paths.writableDirectory'] = $tmpWritable;

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo "<div style='font-family:sans-serif;padding:30px;background:#fff1f2;border:2px solid #e11d48;border-radius:12px;margin:30px auto;max-width:800px;color:#881337;'>";
    echo "<h2 style='margin-top:0;color:#9f1239;'>Detail Error Sistem:</h2>";
    echo "<p><strong>Pesan:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Baris: " . $e->getLine() . ")</p>";
    echo "<pre style='background:#ffe4e6;padding:12px;border-radius:6px;overflow:auto;font-size:12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit;
}


