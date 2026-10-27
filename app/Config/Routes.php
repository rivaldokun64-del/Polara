<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Rute Publik (Homepage & Autentikasi)
$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');



// Rute Terproteksi (Wajib Login)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Home::dashboard', ['as' => 'dashboard']);
    $routes->get('dashboard/dataset-stats/(:num)', 'Home::datasetStats/$1', ['as' => 'dashboard.dataset_stats']);

    // Modul Datasets (Semua peran terautentikasi dapat melihat daftar dataset)
    $routes->get('datasets', 'Datasets::index', ['as' => 'datasets']);
    $routes->post('datasets/upload', 'Datasets::upload', ['filter' => 'role:admin,analis', 'as' => 'datasets.upload']);
    $routes->get('datasets/preview/(:num)', 'Datasets::preview/$1', ['as' => 'datasets.preview']);
    $routes->post('datasets/save-mapping/(:num)', 'Datasets::saveMapping/$1', ['filter' => 'role:admin,analis', 'as' => 'datasets.save_mapping']);
    $routes->get('datasets/delete/(:num)', 'Datasets::delete/$1', ['filter' => 'role:admin,analis', 'as' => 'datasets.delete']);

    // Modul Analisis Data Mining
    $routes->get('analisis', 'Analisis::index', ['as' => 'analisis']);
    $routes->get('analisis/baru', 'Analisis::create', ['filter' => 'role:admin,analis', 'as' => 'analisis.create']);
    $routes->post('analisis/run', 'Analisis::run', ['filter' => 'role:admin,analis', 'as' => 'analisis.run']);
    $routes->get('analisis/detail/(:num)', 'Analisis::detail/$1', ['as' => 'analisis.detail']);
    $routes->get('analisis/delete/(:num)', 'Analisis::delete/$1', ['filter' => 'role:admin,analis', 'as' => 'analisis.delete']);

    // Modul Laporan Rekam Medis
    $routes->get('laporan', 'Laporan::index', ['as' => 'laporan']);
    $routes->get('laporan/export-csv/(:num)', 'Laporan::exportCsv/$1', ['as' => 'laporan.export_csv']);

    // Modul Manajemen Pengguna (Khusus Administrator)
    $routes->get('users', 'Users::index', ['filter' => 'role:admin', 'as' => 'users']);
    $routes->post('users/create', 'Users::create', ['filter' => 'role:admin', 'as' => 'users.create']);
    $routes->get('users/toggle-status/(:num)', 'Users::toggleStatus/$1', ['filter' => 'role:admin', 'as' => 'users.toggle_status']);
});
