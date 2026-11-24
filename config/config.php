<?php
/**
 * Grundkonfiguration. Eigene Anpassungen bitte in config/config.local.php
 * vornehmen (wird nicht ins Repository übernommen), z. B.:
 *
 *   <?php return ['debug' => true, 'mail_to' => 'info@example.de'];
 */
return [
    // Fehler im Browser anzeigen (nur in der Entwicklung aktivieren!)
    'debug'            => false,
    'timezone'         => 'Europe/Berlin',

    // SQLite-Datenbank (Ordner muss für den Webserver beschreibbar sein)
    'db_path'          => APP_ROOT . '/data/database.sqlite',

    // Uploads (Logo, Banner, News-Bilder)
    'upload_dir'       => APP_ROOT . '/public/uploads',
    'upload_url'       => 'uploads',
    'upload_max_bytes' => 5 * 1024 * 1024,

    // Smarty-Verzeichnisse
    'template_dir'     => APP_ROOT . '/templates',
    'compile_dir'      => APP_ROOT . '/var/templates_c',
    'cache_dir'        => APP_ROOT . '/var/cache',

    // Kontaktformular: Empfänger für Benachrichtigungen.
    // Leer lassen = Nachrichten nur im Backend speichern.
    'mail_to'          => '',
    'mail_from'        => 'noreply@localhost',

    // Login-Schutz: max. Fehlversuche je IP innerhalb des Zeitfensters (Minuten)
    'login_max_attempts'   => 5,
    'login_window_minutes' => 15,
];
