<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Settings
{
    /** Felder, die im Backend unter „Allgemein“ bearbeitet werden dürfen. */
    public const TEXT_FIELDS = [
        'site_name', 'site_subtitle', 'slogan', 'meta_description', 'about_title', 'about_text',
        'highlight_title', 'highlight_text', 'menu_note', 'allergen_legend',
        'address', 'phone', 'fax', 'email', 'footer_text',
        'imprint_text', 'privacy_text',
        'contact_email_to', 'contact_email_from',
        'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user',
        'contact_title', 'form_title', 'form_intro', 'form_res_note', 'form_consent',
        'form_hint', 'form_success', 'form_success_res',
    ];

    /** Bild-Einstellungen: Schlüssel => Fallback-Datei in public/assets/img (ohne Endung) */
    public const IMAGE_FIELDS = [
        'logo'         => 'logo',
        'banner'       => 'banner',
        'highlight_image' => 'highlight',
        'about_image'  => 'willkommen',
    ];

    private ?array $cache = null;

    public function __construct(private readonly PDO $db) {}

    public function all(): array
    {
        if ($this->cache === null) {
            $this->cache = $this->db->query('SELECT key, value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        return $this->cache;
    }

    /** Neueste Meldung im Banner zeigen? Standard: ja (auch wenn die Einstellung noch nie gespeichert wurde). */
    public function heroNewsEnabled(): bool
    {
        $all = $this->all();
        return !array_key_exists('hero_news', $all) || $all['hero_news'] === '1';
    }

    public function get(string $key, string $default = ''): string
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value'
        );
        $stmt->execute([$key, $value]);
        $this->cache = null;
    }
}
