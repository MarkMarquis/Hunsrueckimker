<?php
declare(strict_types=1);

namespace App;

final class Format
{
    public static function price(int|string|null $cents): string
    {
        return number_format(((int) $cents) / 100, 2, ',', '.') . ' €';
    }

    /** "12,50" / "12.50" / "12" -> 1250 */
    public static function parsePrice(string $input): int
    {
        $input = trim(str_replace(['€', ' '], '', $input));
        if (str_contains($input, ',')) {
            $input = str_replace('.', '', $input);
            $input = str_replace(',', '.', $input);
        }
        return (int) round(((float) $input) * 100);
    }

    public static function date(?string $date, string $format = 'd.m.Y'): string
    {
        if (!$date) {
            return '';
        }
        $ts = strtotime($date);
        return $ts ? date($format, $ts) : '';
    }

    /**
     * Wandelt Klartext in HTML um: Leerzeile = neuer Absatz, Zeile mit „## “ am
     * Anfang = Zwischenüberschrift. Der Text wird vorher escaped, daher im
     * Template mit "nofilter" ausgeben.
     */
    public static function paragraphs(?string $text): string
    {
        $text = htmlspecialchars(trim((string) $text), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = preg_replace('/^##\s*(.+)$/m', "\n\n##$1\n\n", $text);
        $html = '';
        foreach (preg_split('/\R\s*\R/', $text) ?: [] as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            $html .= str_starts_with($block, '##')
                ? '<h3>' . trim(substr($block, 2)) . '</h3>'
                : '<p>' . nl2br($block, false) . '</p>';
        }
        return $html;
    }

    /** Kurzfassung eines Textes (ohne Zwischenüberschriften), an Wortgrenze gekürzt. */
    public static function excerpt(?string $text, int $length = 140): string
    {
        $text = preg_replace('/^##.*$/m', '', (string) $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        $cut = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');
        return rtrim($space > $length * 0.6 ? mb_substr($cut, 0, $space) : $cut, " ,.;:–-") . ' …';
    }

    /** Text escapen und den Platzhalter {telefon} durch einen Anruf-Link ersetzen. */
    public static function withPhone(?string $text, ?string $phone): string
    {
        $html  = htmlspecialchars(trim((string) $text), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phone = trim((string) $phone);
        $link  = $phone === '' ? '' : '<a href="tel:' . preg_replace('/[^0-9+]/', '', $phone) . '">'
            . htmlspecialchars($phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
        return str_replace('{telefon}', $link, $html);
    }

    /** Zeilen eines Textfelds als Array (z. B. Öffnungszeiten). */
    public static function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text) ?: []), 'strlen'));
    }
}
