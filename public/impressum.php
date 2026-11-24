<?php
declare(strict_types=1);

/** @var App\App $app */
$app = require __DIR__ . '/boot.php';
$app->render('frontend/legal.tpl', [
    'site'     => $app->settings->all(),
    'title'    => 'Impressum',
    'text'     => $app->settings->get('imprint_text'),
    'logo_url' => $app->imageUrl('logo'),
    'year'     => date('Y'),
]);
