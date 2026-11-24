<?php
declare(strict_types=1);

use App\Format;

/** @var App\App $app */
$app = require __DIR__ . '/boot.php';

// Zeitstempel für den Spam-Schutz des Kontaktformulars
$_SESSION['contact_form_started'] = time();

$settings = $app->settings->all();
// Hinweise zu abweichenden Öffnungszeiten stehen immer oben in „Aktuelles“
$news     = array_merge($app->hours->announcements(), $app->news->published(30));
// Nur normale Meldungen zählen für „Ältere Meldungen anzeigen“ (die ersten 3 sind sichtbar)
$regular = 0;
foreach ($news as &$n) {
    $n['hidden'] = empty($n['notice']) && ++$regular > 3;
}
unset($n);

// Bilder für den Bildwechsler; ohne aktive Bilder dient die Bannergrafik als Einzelbild
$slides = array_map(fn ($s) => [
    'url'      => App\App::asset($s['image']),
    'alt'      => $s['alt'],
    'position' => array_key_exists($s['position'], App\SlideRepository::POSITIONS) ? $s['position'] : 'center',
], $app->slides->active());
if (!$slides && ($banner = $app->imageUrl('banner'))) {
    $slides = [['url' => $banner, 'alt' => '', 'position' => 'center']];
}

$app->render('frontend/index.tpl', [
    'site'          => $settings,
    'hours_rows'    => $app->hours->rows(),
    'hours_today'   => $app->hours->todayStatus(),
    'hours_upcoming'=> array_slice($app->hours->exceptions(true), 0, 5),
    'address_lines' => Format::lines($settings['address'] ?? ''),
    'logo_url'      => $app->imageUrl('logo'),
    'slides'        => $slides,
    'highlight_url'    => $app->imageUrl('highlight_image'),
    'about_url'     => $app->imageUrl('about_image'),
    'highlight_menu'   => $app->menu->tree(true, 'highlight'),
    'news'          => $news,
    'regular'       => $regular,
    'hero_news'     => ($news && $app->settings->heroNewsEnabled()) ? $news[0] : null,
    'menu'          => $app->menu->tree(true, 'menu'),
    'contact'       => $_SESSION['contact_result'] ?? null,
    'map_query'     => trim(($settings['address'] ?? '') !== '' ? preg_replace('/\s+/', ' ', $settings['address']) : ''),
    'year'          => date('Y'),
    'today'         => date('Y-m-d'),
    'max_date'      => date('Y-m-d', strtotime('+1 year')),
    'areas'         => App\ContactForm::AREAS,
    'max_persons'   => App\ContactForm::MAX_PERSONS,
]);
unset($_SESSION['contact_result']);
