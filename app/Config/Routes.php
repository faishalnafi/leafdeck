<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─── Landing & SSO ───────────────────────────────────────────────────────────

$routes->get('/', 'Web\HomeController::index');

// SSO (dari portal SSO sekolah) & Auth
$routes->get('sso/login',            'Web\SsoController::login');
$routes->get('sso/google',           'Web\SsoController::googleLogin');
$routes->get('sso/callback',         'Web\SsoController::callback');
$routes->get('sso/logout',           'Web\SsoController::logout');
$routes->get('logout',               'Web\SsoController::logout');
$routes->get('bypass-login',         'Web\SsoController::bypassLogin');
$routes->get('dev-login',            'Web\SsoController::bypassLogin');
$routes->get('dev-login/(:segment)', 'Web\SsoController::bypassLogin/$1');

// Easy Web Installer
$routes->get('install',              'Web\InstallController::index');
$routes->post('install/run',         'Web\InstallController::run');


// ─── Presentasi — u/0 (single account, Google-style) ────────────────────────
//
// Pola URL: /presentation/u/0/
// Mengikuti konvensi Google Docs u/0 untuk single login account.
//
// 📌 BLUEPRINT CATATAN (lihat agents/context/url-blueprint.md):
//   Jika SSO mendukung multi-account di masa depan,
//   pola u/0 akan diperluas ke u/1, u/2, dst.
//   Nilai "0" saat ini diabaikan (hardcoded single session).

$routes->group('presentation/u/(:num)', function ($routes) {

    // Dashboard utama — daftar semua deck milik user
    $routes->get('/',       'Web\DashboardController::index');

    // Tong Sampah — daftar deck yang di-soft-delete
    $routes->get('trash',   'Web\DashboardController::trash/$1');

    // Viewer — tampilkan deck sebagai e-book/presentasi
    // URL: /presentation/u/0/d/{nanoid}/view
    $routes->get('d/(:segment)/view', 'Web\ViewerController::show/$1/$2');

    // Editor metadata (judul, deskripsi, visibilitas)
    // URL: /presentation/u/0/d/{nanoid}/edit
    $routes->get('d/(:segment)/edit', 'Web\DashboardController::editor/$1/$2');

});

// Fallback & direct shortcut routes
$routes->get('presentation/trash',             'Web\DashboardController::trash');
$routes->get('trash',                          'Web\DashboardController::trash');
$routes->get('dashboard',                      'Web\DashboardController::index');
$routes->get('presentation/d/(:segment)/view', 'Web\ViewerController::show/$1');

// ─── Admin Panel Routes ──────────────────────────────────────────────────────
$routes->get('admin',                          'Web\AdminController::index');
$routes->get('admin/dashboard',                'Web\AdminController::index');
$routes->post('admin/settings/sso',            'Web\AdminController::saveSsoSettings');
$routes->post('admin/settings/test-sso',       'Web\AdminController::testSsoConnection');

// Raw Deck HTML renderer for iframe
$routes->get('raw-deck/(:segment)', 'Web\ViewerController::rawHtml/$1');


// ─── Blueprint Routes (disabled — aktifkan saat Phase 2 & 3) ─────────────────
//
// Phase 2: Google Docs-style
// $routes->group('document/u/(:num)', ...);
//
// Phase 3: Google Sheets-style
// $routes->group('spreadsheet/u/(:num)', ...);


// ─── API v1 Routes ────────────────────────────────────────────────────────────

$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // SSO Token Exchange (internal)
    $routes->post('auth/sso',    'AuthController::ssoExchange');
    $routes->post('auth/logout', 'AuthController::logout', ['filter' => 'apiauth']);

    // Admin API
    $routes->group('admin', function ($routes) {
        $routes->post('users/(:num)/role',   'AdminApiController::updateRole/$1');
        $routes->post('users/(:num)/status', 'AdminApiController::toggleStatus/$1');
        $routes->delete('decks/(:segment)',  'AdminApiController::forceDeleteDeck/$1');
    });

    // Decks (semua perlu token)
    $routes->group('decks', ['filter' => 'apiauth'], function ($routes) {
        $routes->get('trash',               'DeckController::trash');
        $routes->get('/',                   'DeckController::index');
        $routes->post('/',                  'DeckController::create');
        $routes->get('(:segment)',          'DeckController::show/$1');
        $routes->put('(:segment)',          'DeckController::update/$1');
        $routes->post('(:segment)/restore', 'DeckController::restore/$1');
        $routes->delete('(:segment)/force', 'DeckController::forceDelete/$1');
        $routes->delete('(:segment)',       'DeckController::delete/$1');
    });

    // Users (semua perlu token)
    $routes->group('users', ['filter' => 'apiauth'], function ($routes) {
        $routes->get('profile',    'UserController::profile');
        $routes->put('profile',    'UserController::updateProfile');
    });
});

