<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require __DIR__ . '/requirements.php';

// Server-Voraussetzungen prüfen, bevor Composer geladen wird
$problems = app_requirement_problems(APP_ROOT);
if ($problems) {
    app_render_error_page(
        'Einrichtung unvollständig',
        '<p>Die Webseite kann noch nicht starten. Bitte folgende Punkte beheben:</p><ul><li>'
        . implode('</li><li>', $problems) . '</li></ul>'
        . '<p>Danach diese Seite neu laden.</p>'
    );
    exit;
}

$localConfig = APP_ROOT . '/config/config.local.php';
$debug = is_file($localConfig) && !empty((require $localConfig)['debug']);
app_install_error_handlers(APP_ROOT, $debug);

require APP_ROOT . '/vendor/autoload.php';

$config = require APP_ROOT . '/config/config.php';
if (is_file(APP_ROOT . '/config/config.local.php')) {
    $config = array_replace($config, require APP_ROOT . '/config/config.local.php');
}

error_reporting(E_ALL);
ini_set('display_errors', $config['debug'] ? '1' : '0');
date_default_timezone_set($config['timezone']);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name('site_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

return new App\App($config);
