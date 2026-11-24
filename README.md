# Der Hunsrück-Imker – Webseite

Onepager für die Imkerei **Der Hunsrück-Imker – Mark Marquis** (Herborn) mit kleinem Backend zur Pflege der Inhalte.
Technisch identisch mit der Webseite „Das original Spießbratenhaus“.

- Live (bisher TYPO3): https://hunsrueck-imker.de
- Entwicklung: https://neu.hunsrueck-imker.de

**Technik:** PHP 8.1+, Smarty 5, jQuery 3.7 (lokal eingebunden), SQLite (keine separate Datenbank nötig).

## Funktionen

**Webseite (Onepager)**
- Banner mit Logo, Name, Untertitel, Slogan und Buttons (Speisekarte, Anrufen)
- Über uns
- **Imkerei-Bereich** (dunkel abgesetzt): die Trachten rund um Herborn mit Bild
- Aktuelles (News) mit „Weiterlesen“ und „Ältere Meldungen anzeigen“
- **Honigsorten** mit Foto, Glasgröße, Preis (Preis 0 = ausgeblendet) und Hinweisen wie „cremig“ oder „flüssig“
- Verkaufszeiten, Adresse und **Kontakt- & Bestellformular** (Allgemeine Anfrage oder Honigbestellung mit Lieferart, Anzahl Gläser und Wunschtermin; AJAX, Spam-Schutz per Honeypot und Mindestzeit, DSGVO-Einwilligung)
- Impressum- und Datenschutzseite
- Responsiv, mit sanftem Scrollen; ohne externe Fonts oder CDNs (DSGVO-freundlich)

**Backend** (`/admin/`)
- Erstinstallation: Beim ersten Aufruf wird das Administrator-Konto angelegt.
- Einstellungen: Name, Texte, Logo, Banner, Bild für den Highlight-Bereich, Öffnungszeiten, Kontaktdaten, Impressum, Datenschutz
- News anlegen, bearbeiten, löschen, zeitgesteuert veröffentlichen, mit Bild
- Speisekarte: Kategorien je Bereich („Highlight-Bereich“ oder „Speisekarte“), Einträge mit Portion/Gewicht, weiteren Preisvarianten, Allergenen und Hinweisen; Sortierung per Drag & Drop, Einträge per Schalter ein- und ausblenden
- Nachrichten aus dem Kontaktformular lesen, beantworten (per E-Mail-Link) und löschen
- Passwort ändern

**Sicherheit:** Passwörter mit `password_hash`, CSRF-Token für alle Formulare, Sperre nach mehreren Fehlversuchen beim Login,
Session-Regeneration, automatisches HTML-Escaping in Smarty, geprüfte Bild-Uploads (keine SVG, zufällige Dateinamen),
PHP-Ausführung im Upload-Ordner gesperrt.

## Installation

```bash
composer install --no-dev
```

1. Den Webserver (Document Root) der Domain auf den Ordner **`public/`** zeigen lassen
   (bei IONOS: *Domains & SSL → Domain → Webspace verbinden → Verzeichnis `…/public`*).
   Im Hauptordner darf **keine** `.htaccess` mit Umleitungen liegen – sie würde auch für `public/` gelten
   und jede Anfrage ins Leere umleiten.
2. Schreibrechte für den Webserver setzen auf: `data/`, `var/templates_c/`, `var/cache/`, `public/uploads/`
3. `https://ihre-domain.de/admin/` aufrufen und das Administrator-Konto anlegen.

Die Datenbank (`data/database.sqlite`) wird beim ersten Aufruf automatisch angelegt
und mit den Inhalten aus `src/SeedData.php` befüllt (Texte der bisherigen Webseite, Honigsorten und Preise 2026).

**Vor dem Livegang prüfen:** In der **Datenschutzerklärung** (Abschnitt 3) den Hosting-Anbieter eintragen.

### Lokal testen

```bash
php -S localhost:8080 -t public
```

## Veröffentlichen per SFTP (GitHub Actions)

Der Workflow `.github/workflows/deploy.yml` installiert die Abhängigkeiten und lädt alles per SFTP hoch.

1. In GitHub unter *Settings → Secrets and variables → Actions* die Secrets `FTP_SERVER`, `FTP_USERNAME`,
   `FTP_PASSWORD` (und optional `FTP_PORT`, Standard 22) anlegen.
2. Optional unter *Variables* `SFTP_REMOTE_DIR` mit dem Zielordner anlegen (z. B. `/httpdocs`).
3. *Actions → Deploy (SFTP) → Run workflow*. Standardmäßig ist „Nur testen“ angehakt: Das Protokoll zeigt dann,
   was hochgeladen würde. Für den echten Upload den Haken entfernen.

Auf dem Server wird nichts gelöscht; Datenbank, hochgeladene Bilder und `config/config.local.php` bleiben unberührt.

## Logo und Bilder

| Bild | Standarddatei | Im Backend austauschbar |
|------|---------------|-------------------------|
| Logo | `public/assets/img/logo.png` (oder `.jpg`/`.webp`) | Einstellungen → Logo & Bilder |
| Banner | `public/assets/img/banner.jpg` | Einstellungen → Logo & Bilder |
| Highlight-Bereich | `public/assets/img/highlight.jpg` | Einstellungen → Logo & Bilder |

Ein im Backend hochgeladenes Bild hat immer Vorrang vor der Standarddatei.
Bannerbilder: `public/assets/img/slides/`, Fotos der Honigsorten: `public/assets/img/honig/`.
Das Logo wird am besten als PNG mit weißem oder transparentem Hintergrund und ca. 900 px Breite hinterlegt.

## Farben und Schriften

Farben, Schriften und Abstände stehen als CSS-Variablen am Anfang von `public/assets/css/style.css` (`:root`).

## Konfiguration

Grundeinstellungen stehen in `config/config.php`. Eigene Werte gehören in `config/config.local.php`
(diese Datei wird nicht ins Repository übernommen), z. B. um Kontaktanfragen zusätzlich per E-Mail zu erhalten:

```php
<?php
return [
    'mail_to'   => 'info@ihre-domain.de',
    'mail_from' => 'webseite@ihre-domain.de',
];
```

## Texte formatieren

In mehrzeiligen Textfeldern (Über uns, Highlight-Bereich, News, Impressum, Datenschutz) gilt:
- Leerzeile = neuer Absatz
- Zeile beginnt mit `## ` = Zwischenüberschrift

## Verzeichnisstruktur

```
config/       Konfiguration
data/         SQLite-Datenbank
public/       Document Root (index.php, contact.php, admin/, assets/, uploads/)
src/          PHP-Klassen (Datenbank, Repositories, Auth, Upload, Kontaktformular)
templates/    Smarty-Templates (frontend/, admin/, partials/)
var/          Smarty-Cache und kompilierte Templates
```
