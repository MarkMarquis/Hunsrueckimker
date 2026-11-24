<?php
declare(strict_types=1);

namespace App;

use PDO;
use Smarty\Smarty;

/**
 * Kleiner Service-Container: hält Konfiguration, Datenbank und Smarty.
 */
final class App
{
    public readonly PDO $db;
    public readonly Smarty $view;
    public readonly Settings $settings;
    public readonly NewsRepository $news;
    public readonly MenuRepository $menu;
    public readonly MessageRepository $messages;
    public readonly SlideRepository $slides;
    public readonly OpeningHours $hours;
    public readonly Auth $auth;
    public readonly Upload $upload;

    public function __construct(public readonly array $config)
    {
        $this->db       = Database::connect($config['db_path']);
        Database::migrate($this->db);

        $this->settings = new Settings($this->db);
        $this->news     = new NewsRepository($this->db);
        $this->menu     = new MenuRepository($this->db);
        $this->messages = new MessageRepository($this->db);
        $this->slides   = new SlideRepository($this->db);
        $this->hours    = new OpeningHours($this->db, $this->settings);
        $this->auth     = new Auth($this->db, $config);
        $this->upload   = new Upload($config);
        $this->view     = $this->createView();
    }

    private function createView(): Smarty
    {
        $smarty = new Smarty();
        $smarty->setTemplateDir($this->config['template_dir']);
        $smarty->setCompileDir($this->config['compile_dir']);
        $smarty->setCacheDir($this->config['cache_dir']);
        // Alle Ausgaben automatisch HTML-escapen (XSS-Schutz)
        $smarty->setEscapeHtml(true);
        $smarty->setCompileCheck(Smarty::COMPILECHECK_ON);

        $smarty->registerPlugin('modifier', 'price', [Format::class, 'price']);
        $smarty->registerPlugin('modifier', 'date_de', [Format::class, 'date']);
        $smarty->registerPlugin('modifier', 'paragraphs', [Format::class, 'paragraphs']);
        $smarty->registerPlugin('modifier', 'excerpt', [Format::class, 'excerpt']);
        $smarty->registerPlugin('modifier', 'asset', [self::class, 'asset']);
        $smarty->registerPlugin('modifier', 'with_phone', [Format::class, 'withPhone']);
        $smarty->registerPlugin('modifier', 'weekday_de', [ContactForm::class, 'weekday']);
        $smarty->registerPlugin('modifier', 'area_label', [ContactForm::class, 'areaLabel']);
        // Foto einer Sorte: mitgelieferte Datei (assets/…) oder Upload aus dem Backend
        $uploadUrl = $this->config['upload_url'];
        $smarty->registerPlugin('modifier', 'item_image', fn (?string $img, string $base = '') =>
            $img ? self::asset($base . (str_starts_with($img, 'assets/') ? $img : $uploadUrl . '/' . $img)) : '');
        $smarty->registerPlugin('modifier', 'nl2br', 'nl2br');
        $smarty->registerPlugin('modifier', 'urlencode', 'rawurlencode');

        $smarty->assign('upload_url', $this->config['upload_url']);
        return $smarty;
    }

    /**
     * URL eines Bildes: Upload aus dem Backend > public/assets/img/<fallback>.(jpg|png|webp|svg) > null.
     * $base ist der relative Pfad zum public-Verzeichnis ('' im Frontend, '../' im Backend).
     */
    public function imageUrl(string $key, string $base = ''): ?string
    {
        if ($file = $this->settings->get($key)) {
            return self::asset($base . $this->config['upload_url'] . '/' . $file);
        }
        $fallback = Settings::IMAGE_FIELDS[$key] ?? $key;
        foreach (['png', 'jpg', 'webp', 'svg'] as $ext) {
            if (is_file(APP_ROOT . '/public/assets/img/' . $fallback . '.' . $ext)) {
                return self::asset($base . 'assets/img/' . $fallback . '.' . $ext);
            }
        }
        return null;
    }

    /**
     * Hängt an eine Datei-Adresse (relativ zu public/, optional mit „../“) eine Versionskennung
     * aus dem Änderungsdatum an. Ändert sich die Datei, ändert sich die Adresse – Browser laden
     * dann sofort die neue Fassung statt einer zwischengespeicherten.
     */
    public static function asset(string $url): string
    {
        $path = APP_ROOT . '/public/' . preg_replace('#^(\.\./)+#', '', strtok($url, '?'));
        $mtime = is_file($path) ? filemtime($path) : false;
        return $mtime ? $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $mtime : $url;
    }

    public function render(string $template, array $vars = []): void
    {
        $this->view->assign($vars);
        $this->view->assign('flash', Flash::pull());
        $this->view->assign('csrf_token', Csrf::token());
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        $this->view->display($template);
    }
}
