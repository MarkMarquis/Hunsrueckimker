<?php
declare(strict_types=1);

/** @var App\App $app */
$app = require __DIR__ . '/boot.php';
$app->render('frontend/legal.tpl', [
    'site'     => $app->settings->all(),
    'title'    => 'Datenschutz',
    'text'     => $app->settings->get('privacy_text'),
    'logo_url' => $app->imageUrl('logo'),
    'year'     => date('Y'),
]);
