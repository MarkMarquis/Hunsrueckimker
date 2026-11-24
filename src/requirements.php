<?php
/**
 * Prüft die Server-Voraussetzungen, BEVOR Composer/Smarty geladen werden.
 * Bewusst in einfacher PHP-Syntax geschrieben, damit die Prüfung auch auf
 * zu alten PHP-Versionen läuft und eine verständliche Meldung anzeigt,
 * statt einer weißen Seite.
 */

function app_requirement_problems($root)
{
    $problems = array();

    if (version_compare(PHP_VERSION, '8.1.0', '<')) {
        $problems[] = 'Die PHP-Version ist ' . PHP_VERSION . ', benötigt wird mindestens <strong>PHP 8.1</strong> '
            . '(empfohlen 8.2 oder 8.3). Bitte im Kundenmenü des Hosters für diese Domain umstellen.';
    }
    foreach (array('pdo_sqlite' => 'PDO SQLite', 'mbstring' => 'mbstring', 'fileinfo' => 'fileinfo') as $ext => $label) {
        if (!extension_loaded($ext)) {
            $problems[] = 'Die PHP-Erweiterung <strong>' . $label . '</strong> (' . $ext . ') ist nicht aktiviert. '
                . 'Bitte im Kundenmenü des Hosters aktivieren.';
        }
    }
    if (!is_file($root . '/vendor/autoload.php')) {
        $problems[] = 'Der Ordner <strong>vendor/</strong> fehlt. Bitte den Deploy-Workflow erneut ausführen '
            . 'oder lokal <code>composer install</code> ausführen und vendor/ hochladen.';
    }
    foreach (array('data', 'var/templates_c', 'var/cache', 'public/uploads') as $dir) {
        $path = $root . '/' . $dir;
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
        if (!is_dir($path) || !is_writable($path)) {
            $problems[] = 'Der Ordner <strong>' . $dir . '/</strong> ist für PHP nicht beschreibbar. '
                . 'Bitte per SFTP die Rechte auf 775 (bzw. 777, falls 775 nicht reicht) setzen.';
        }
    }
    $db = $root . '/data/database.sqlite';
    if (is_file($db) && !is_writable($db)) {
        $problems[] = 'Die Datei <strong>data/database.sqlite</strong> ist nicht beschreibbar. Bitte Rechte auf 664 setzen.';
    }
    return $problems;
}

function app_render_error_page($title, $html)
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
    }
    echo '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">'
        . '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>'
        . '<style>body{margin:0;background:#f6f1e7;color:#1f1c18;font:16px/1.6 system-ui,sans-serif}'
        . 'main{max-width:680px;margin:8vh auto;padding:0 16px}h1{font-family:Georgia,serif;color:#8a1f1d}'
        . 'li{margin:.6em 0}code{background:#ece4d3;padding:1px 5px;border-radius:3px}</style></head><body><main>'
        . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>' . $html . '</main></body></html>';
}

/** Fehler in data/error.log schreiben (per SFTP abrufbar, per .htaccess vor Web-Zugriff geschützt). */
function app_log_error($root, $message)
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    if (!@file_put_contents($root . '/data/error.log', $line, FILE_APPEND | LOCK_EX)) {
        error_log($message);
    }
}

function app_install_error_handlers($root, $debug)
{
    set_exception_handler(function ($e) use ($root, $debug) {
        $id = substr(md5(uniqid('', true)), 0, 8);
        app_log_error($root, 'Fehler-ID ' . $id . ': ' . get_class($e) . ': ' . $e->getMessage()
            . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
        $detail = $debug
            ? '<pre style="white-space:pre-wrap;font-size:13px">' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>'
            : '';
        app_render_error_page(
            'Die Seite ist vorübergehend nicht erreichbar',
            '<p>Es ist ein technischer Fehler aufgetreten. Bitte versuchen Sie es später erneut.</p>'
            . '<p style="color:#7a7164;font-size:.9em">Fehler-ID: ' . $id . ' – Details stehen in <code>data/error.log</code>.</p>'
            . $detail
        );
    });

    register_shutdown_function(function () use ($root) {
        $err = error_get_last();
        if ($err && in_array($err['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
            app_log_error($root, 'Fataler Fehler: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
            if (!headers_sent()) {
                app_render_error_page(
                    'Die Seite ist vorübergehend nicht erreichbar',
                    '<p>Es ist ein technischer Fehler aufgetreten. Details stehen in <code>data/error.log</code>.</p>'
                );
            }
        }
    });
}
