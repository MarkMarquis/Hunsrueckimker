<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    public static function connect(string $path): PDO
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        // Kein WAL-Modus: Er funktioniert auf Netzwerk-Dateisystemen (z. B. IONOS-Webspace) nicht zuverlässig.
        // Der Standardmodus DELETE ist für die geringe Last einer kleinen Webseite völlig ausreichend.
        $pdo->exec('PRAGMA journal_mode = DELETE');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        return $pdo;
    }

    /** Legt Tabellen an (idempotent) und befüllt eine leere Datenbank mit Beispielinhalten. */
    public static function migrate(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS settings (
                key   TEXT PRIMARY KEY,
                value TEXT NOT NULL DEFAULT ''
            );
            CREATE TABLE IF NOT EXISTS users (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                username      TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS login_attempts (
                ip           TEXT NOT NULL,
                attempted_at INTEGER NOT NULL
            );
            CREATE TABLE IF NOT EXISTS news (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                title        TEXT NOT NULL,
                content      TEXT NOT NULL DEFAULT '',
                image        TEXT NOT NULL DEFAULT '',
                published    INTEGER NOT NULL DEFAULT 1,
                published_at TEXT NOT NULL,
                created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS menu_categories (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                name        TEXT NOT NULL,
                description TEXT NOT NULL DEFAULT '',
                sort_order  INTEGER NOT NULL DEFAULT 0
            );
            CREATE TABLE IF NOT EXISTS menu_items (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL REFERENCES menu_categories(id) ON DELETE CASCADE,
                name        TEXT NOT NULL,
                description TEXT NOT NULL DEFAULT '',
                price_cents INTEGER NOT NULL DEFAULT 0,
                tags        TEXT NOT NULL DEFAULT '',
                active      INTEGER NOT NULL DEFAULT 1,
                sort_order  INTEGER NOT NULL DEFAULT 0
            );
            CREATE TABLE IF NOT EXISTS messages (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                name       TEXT NOT NULL,
                email      TEXT NOT NULL,
                phone      TEXT NOT NULL DEFAULT '',
                subject    TEXT NOT NULL DEFAULT '',
                message    TEXT NOT NULL,
                is_read    INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS opening_exceptions (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                title         TEXT NOT NULL DEFAULT '',
                date_from     TEXT NOT NULL,
                date_to       TEXT NOT NULL DEFAULT '',
                type          TEXT NOT NULL DEFAULT 'closed',
                open_time     TEXT NOT NULL DEFAULT '',
                close_time    TEXT NOT NULL DEFAULT '',
                note          TEXT NOT NULL DEFAULT '',
                announce_days INTEGER NOT NULL DEFAULT 28
            );
            CREATE TABLE IF NOT EXISTS slides (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                image      TEXT NOT NULL,
                alt        TEXT NOT NULL DEFAULT '',
                position   TEXT NOT NULL DEFAULT 'center',
                active     INTEGER NOT NULL DEFAULT 1,
                builtin    INTEGER NOT NULL DEFAULT 0,
                sort_order INTEGER NOT NULL DEFAULT 0
            );
            CREATE INDEX IF NOT EXISTS idx_menu_items_cat ON menu_items(category_id);
        SQL);

        self::addColumns($pdo, 'menu_categories', [
            'section' => "TEXT NOT NULL DEFAULT 'menu'",
        ]);
        self::addColumns($pdo, 'messages', [
            'mail_error' => "TEXT NOT NULL DEFAULT ''",
            'type'       => "TEXT NOT NULL DEFAULT 'anfrage'",
            'res_date'   => "TEXT NOT NULL DEFAULT ''",
            'res_time'   => "TEXT NOT NULL DEFAULT ''",
            'persons'    => "INTEGER NOT NULL DEFAULT 0",
            'area'       => "TEXT NOT NULL DEFAULT ''",
        ]);
        self::addColumns($pdo, 'menu_items', [
            'allergens' => "TEXT NOT NULL DEFAULT ''",
            'portion'   => "TEXT NOT NULL DEFAULT ''",
            'variants'  => "TEXT NOT NULL DEFAULT '[]'",
        ]);

        $hasSettings = (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
        if ($hasSettings === 0) {
            self::seed($pdo);
        }

        // Feste Wochenöffnungszeiten anlegen bzw. auf die aktuellen Standardzeiten bringen.
        // Bereits im Backend geänderte Zeiten werden dabei nicht überschrieben.
        $weekVersion = (int) ($pdo->query("SELECT value FROM settings WHERE key = 'opening_week_version'")->fetchColumn() ?: 0);
        if ($weekVersion < OpeningHours::WEEK_VERSION) {
            $current = $pdo->query("SELECT value FROM settings WHERE key = 'opening_week'")->fetchColumn();
            $decoded = $current === false ? null : json_decode((string) $current, true);
            $unchanged = $current === false || $decoded == OpeningHours::legacyDefaultWeek();
            if ($unchanged) {
                $pdo->prepare("INSERT INTO settings (key, value) VALUES ('opening_week', ?)
                               ON CONFLICT(key) DO UPDATE SET value = excluded.value")
                    ->execute([json_encode(OpeningHours::defaultWeek())]);
            }
            $pdo->prepare("INSERT INTO settings (key, value) VALUES ('opening_note', ?) ON CONFLICT(key) DO NOTHING")
                ->execute(['']);
            $pdo->prepare("INSERT INTO settings (key, value) VALUES ('opening_week_version', ?)
                           ON CONFLICT(key) DO UPDATE SET value = excluded.value")
                ->execute([(string) OpeningHours::WEEK_VERSION]);
        }

        // Textblöcke des Kontaktformulars anlegen, falls noch nicht vorhanden
        $insertText = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO NOTHING');
        foreach (SeedData::contactTexts() as $key => $value) {
            $insertText->execute([$key, $value]);
        }

        // Mitgeliefertes Impressum aktualisieren – nur solange noch der alte Platzhalter
        // bzw. eine frühere mitgelieferte Fassung drinsteht (eigene Änderungen bleiben erhalten).
        $imprintVersion = (int) ($pdo->query("SELECT value FROM settings WHERE key = 'imprint_version'")->fetchColumn() ?: 0);
        if ($imprintVersion < SeedData::IMPRINT_VERSION) {
            $current = (string) $pdo->query("SELECT value FROM settings WHERE key = 'imprint_text'")->fetchColumn();
            $isDefault = $current === '' || str_contains($current, '[Inhaber / Firma]')
                || str_contains($current, 'Bitte im Backend unter „Einstellungen“ vervollständigen');
            if ($isDefault) {
                $pdo->prepare("INSERT INTO settings (key, value) VALUES ('imprint_text', ?)
                               ON CONFLICT(key) DO UPDATE SET value = excluded.value")->execute([SeedData::imprint()]);
            }
            $pdo->prepare("INSERT INTO settings (key, value) VALUES ('imprint_version', ?)
                           ON CONFLICT(key) DO UPDATE SET value = excluded.value")->execute([(string) SeedData::IMPRINT_VERSION]);
        }

        // Mitgelieferte Datenschutzerklärung einsetzen – nur solange noch der Platzhalter drinsteht
        $privacyVersion = (int) ($pdo->query("SELECT value FROM settings WHERE key = 'privacy_version'")->fetchColumn() ?: 0);
        if ($privacyVersion < SeedData::PRIVACY_VERSION) {
            $current = (string) $pdo->query("SELECT value FROM settings WHERE key = 'privacy_text'")->fetchColumn();
            if ($current === '' || str_contains($current, 'Bitte hier die vollständige Datenschutzerklärung einfügen')) {
                $pdo->prepare("INSERT INTO settings (key, value) VALUES ('privacy_text', ?)
                               ON CONFLICT(key) DO UPDATE SET value = excluded.value")->execute([SeedData::privacy()]);
            }
            $pdo->prepare("INSERT INTO settings (key, value) VALUES ('privacy_version', ?)
                           ON CONFLICT(key) DO UPDATE SET value = excluded.value")->execute([(string) SeedData::PRIVACY_VERSION]);
        }

        // Start-Bannerbilder mit der mitgelieferten Liste abgleichen:
        // fehlende ergänzen, nicht mehr enthaltene entfernen, Reihenfolge übernehmen.
        // Einstellungen bestehender Bilder (Beschreibung, Ausschnitt, ein/aus) bleiben erhalten,
        // selbst hochgeladene Bilder (builtin = 0) werden nicht angefasst.
        $seeded = (int) ($pdo->query("SELECT value FROM settings WHERE key = 'slides_seeded'")->fetchColumn() ?: 0);
        if ($seeded < SeedData::SLIDES_VERSION) {
            $pdo->beginTransaction();
            $wanted = array_column(SeedData::slides(), 0);
            $existing = $pdo->query('SELECT id, image FROM slides WHERE builtin = 1')->fetchAll(PDO::FETCH_KEY_PAIR);
            $delete = $pdo->prepare('DELETE FROM slides WHERE id = ?');
            foreach ($existing as $id => $image) {
                if (!in_array($image, $wanted, true)) {
                    $delete->execute([$id]);
                }
            }
            $insert = $pdo->prepare('INSERT INTO slides (image, alt, position, active, builtin, sort_order) VALUES (?, ?, ?, 1, 1, ?)');
            $order  = $pdo->prepare('UPDATE slides SET sort_order = ? WHERE builtin = 1 AND image = ?');
            foreach (SeedData::slides() as $i => [$image, $alt, $position]) {
                $sort = ($i + 1) * 10 - 1000;
                if (in_array($image, $existing, true)) {
                    $order->execute([$sort, $image]);
                } else {
                    $insert->execute([$image, $alt, $position, $sort]);
                }
            }
            $pdo->prepare("INSERT INTO settings (key, value) VALUES ('slides_seeded', ?)
                           ON CONFLICT(key) DO UPDATE SET value = excluded.value")
                ->execute([(string) SeedData::SLIDES_VERSION]);
            $pdo->commit();
        }
    }

    private static function seed(PDO $pdo): void
    {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
        foreach (SeedData::settings() as $k => $v) {
            $stmt->execute([$k, $v]);
        }

        $stmt = $pdo->prepare('INSERT INTO news (title, content, published, published_at) VALUES (?, ?, 1, ?)');
        foreach (SeedData::news() as [$title, $content, $daysAgo]) {
            $stmt->execute([$title, $content, date('Y-m-d', strtotime("-{$daysAgo} days"))]);
        }

        $catStmt  = $pdo->prepare('INSERT INTO menu_categories (name, description, section, sort_order) VALUES (?, ?, ?, ?)');
        $itemStmt = $pdo->prepare(
            'INSERT INTO menu_items (category_id, name, description, price_cents, allergens, tags, portion, variants, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach (SeedData::menu() as $ci => [$cat, $desc, $section, $items]) {
            $catStmt->execute([$cat, $desc, $section, ($ci + 1) * 10]);
            $catId = (int) $pdo->lastInsertId();
            foreach ($items as $ii => [$name, $idesc, $price, $allergens, $tags, $portion, $variants]) {
                $variants = array_map(fn ($v) => ['label' => $v[0], 'price_cents' => $v[1]], $variants);
                $itemStmt->execute([
                    $catId, $name, $idesc, $price, $allergens, $tags, $portion,
                    json_encode($variants, JSON_UNESCAPED_UNICODE), ($ii + 1) * 10,
                ]);
            }
        }

        $pdo->commit();
    }

    /** Fügt Spalten hinzu, die in älteren Datenbanken noch fehlen. */
    private static function addColumns(PDO $pdo, string $table, array $columns): void
    {
        $existing = array_column($pdo->query("PRAGMA table_info({$table})")->fetchAll(), 'name');
        foreach ($columns as $name => $definition) {
            if (!in_array($name, $existing, true)) {
                $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$name} {$definition}");
            }
        }
    }
}
