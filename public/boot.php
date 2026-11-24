<?php
/**
 * Einstiegspunkt für alle Seiten. Liegt bewusst in public/, damit auch dann
 * eine verständliche Meldung erscheint, wenn PHP die Programmdateien
 * außerhalb von public/ nicht lesen darf (z. B. wegen open_basedir).
 */
$appBootstrap = dirname(__DIR__) . '/src/bootstrap.php';

if (!@is_file($appBootstrap) || !@is_readable($appBootstrap)) {
    $basedir = (string) ini_get('open_basedir');
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
    }
    echo '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="robots" content="noindex">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1"><title>Einrichtung unvollständig</title>'
        . '<style>body{margin:0;background:#f6f1e7;color:#1f1c18;font:16px/1.6 system-ui,sans-serif}'
        . 'main{max-width:680px;margin:8vh auto;padding:0 16px}h1{font-family:Georgia,serif;color:#8a1f1d}'
        . 'code{background:#ece4d3;padding:1px 5px;border-radius:3px}</style></head><body><main>'
        . '<h1>Einrichtung unvollständig</h1>'
        . '<p>PHP kann die Programmdateien im Ordner <code>src/</code> eine Ebene über <code>public/</code> nicht lesen.</p>';
    if ($basedir !== '') {
        echo '<p>Der Hoster beschränkt PHP per <strong>open_basedir</strong> auf: <code>'
            . htmlspecialchars($basedir, ENT_QUOTES, 'UTF-8') . '</code></p>'
            . '<p>Bitte im Kundenmenü des Hosters <code>open_basedir</code> für diese Domain so einstellen, dass auch der '
            . 'übergeordnete Ordner (FTP-Root) erlaubt ist – oft gibt es dafür eine Option wie „open_basedir: Webspace-Root“ '
            . 'oder „none“.</p>';
    } else {
        echo '<p>Bitte prüfen, ob die Ordner <code>src/</code>, <code>vendor/</code>, <code>config/</code> usw. '
            . 'auf derselben Ebene wie <code>public/</code> liegen.</p>';
    }
    echo '</main></body></html>';
    exit;
}

return require $appBootstrap;
