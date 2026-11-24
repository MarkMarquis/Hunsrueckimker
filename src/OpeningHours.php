<?php
declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;

/**
 * Öffnungszeiten: feste Wochenzeiten (Einstellung „opening_week“ als JSON)
 * und Ausnahmen (Tabelle opening_exceptions), z. B. an Feiertagen.
 * Ausnahmen erzeugen ab einer Vorlaufzeit automatisch einen Hinweis unter „Aktuelles“.
 */
final class OpeningHours
{
    /** ISO-Wochentag (1 = Montag) => Name */
    public const DAYS = [1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag', 5 => 'Freitag', 6 => 'Samstag', 7 => 'Sonntag'];

    public const TYPES = [
        'closed' => 'Geschlossen',
        'open'   => 'Ausnahmsweise geöffnet',
        'hours'  => 'Geänderte Öffnungszeiten',
    ];

    public const DEFAULT_ANNOUNCE_DAYS = 28;

    public function __construct(private readonly PDO $db, private readonly Settings $settings) {}

    /** Version der Standard-Wochenzeiten (für die automatische Aktualisierung bestehender Installationen) */
    public const WEEK_VERSION = 2;

    /** Standard-Verkaufszeiten: Mo–Do ab 18 Uhr, Fr–So ganztags (ohne Uhrzeit) */
    public static function defaultWeek(): array
    {
        $week = [];
        foreach (self::DAYS as $d => $name) {
            $week[$d] = [
                'closed' => false,
                'open'   => $d <= 4 ? '18:00' : '',
                'close'  => '',
            ];
        }
        return $week;
    }

    /** Erste Fassung der Standardzeiten (ohne Schließzeit) – zum Erkennen unveränderter Daten */
    public static function legacyDefaultWeek(): array
    {
        $week = [];
        foreach (self::DAYS as $d => $name) {
            $week[$d] = ['closed' => in_array($d, [3, 4], true), 'open' => '11:00', 'close' => ''];
        }
        return $week;
    }

    public function week(): array
    {
        $stored = json_decode($this->settings->get('opening_week'), true);
        $week = self::defaultWeek();
        if (is_array($stored)) {
            foreach (self::DAYS as $d => $_) {
                if (isset($stored[$d]) && is_array($stored[$d])) {
                    $week[$d] = [
                        'closed' => !empty($stored[$d]['closed']),
                        'open'   => self::time($stored[$d]['open'] ?? ''),
                        'close'  => self::time($stored[$d]['close'] ?? ''),
                    ];
                }
            }
        }
        return $week;
    }

    public function saveWeek(array $week): void
    {
        $clean = [];
        foreach (self::DAYS as $d => $_) {
            $row = $week[$d] ?? [];
            $clean[$d] = [
                'closed' => !empty($row['closed']),
                'open'   => self::time($row['open'] ?? ''),
                'close'  => self::time($row['close'] ?? ''),
            ];
        }
        $this->settings->set('opening_week', json_encode($clean));
    }

    /** "11:00" oder '' */
    public static function time(mixed $v): string
    {
        $v = trim((string) $v);
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : '';
    }

    public static function span(string $open, string $close): string
    {
        if ($open === '' && $close === '') {
            return 'ganztags';
        }
        if ($close === '') {
            return 'ab ' . $open . ' Uhr';
        }
        return ($open !== '' ? $open : '') . ' – ' . $close . ' Uhr';
    }

    /**
     * Zeilen für die Anzeige: gleiche aufeinanderfolgende Tage werden zusammengefasst
     * (z. B. „Freitag – Sonntag: ab 11:00 Uhr“).
     */
    public function rows(?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        $todayDow = (int) $today->format('N');
        $rows = [];
        foreach ($this->week() as $d => $w) {
            $text = $w['closed'] ? 'kein Verkauf' : self::span($w['open'], $w['close']);
            $last = count($rows) - 1;
            if ($last >= 0 && $rows[$last]['text'] === $text && $rows[$last]['to'] === $d - 1) {
                $rows[$last]['to'] = $d;
            } else {
                $rows[] = ['from' => $d, 'to' => $d, 'text' => $text, 'closed' => $w['closed']];
            }
        }
        foreach ($rows as &$r) {
            $r['label'] = $r['from'] === $r['to'] ? self::DAYS[$r['from']]
                : self::DAYS[$r['from']] . ($r['to'] - $r['from'] === 1 ? ' & ' : ' – ') . self::DAYS[$r['to']];
            $r['is_today'] = $todayDow >= $r['from'] && $todayDow <= $r['to'];
        }
        return $rows;
    }

    // ----- Ausnahmen -----------------------------------------------------------

    public function exceptions(bool $upcomingOnly = false): array
    {
        $sql = 'SELECT * FROM opening_exceptions'
            . ($upcomingOnly ? " WHERE COALESCE(NULLIF(date_to, ''), date_from) >= date('now', 'localtime')" : '')
            . ' ORDER BY date_from, id';
        return array_map([$this, 'decorate'], $this->db->query($sql)->fetchAll());
    }

    public function findException(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM opening_exceptions WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->decorate($row) : null;
    }

    public function saveException(array $d, ?int $id = null): int
    {
        $params = [$d['title'], $d['date_from'], $d['date_to'], $d['type'], $d['open_time'], $d['close_time'], $d['note'], $d['announce_days']];
        if ($id) {
            $params[] = $id;
            $this->db->prepare(
                'UPDATE opening_exceptions SET title = ?, date_from = ?, date_to = ?, type = ?, open_time = ?, close_time = ?,
                 note = ?, announce_days = ? WHERE id = ?'
            )->execute($params);
            return $id;
        }
        $this->db->prepare(
            'INSERT INTO opening_exceptions (title, date_from, date_to, type, open_time, close_time, note, announce_days)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute($params);
        return (int) $this->db->lastInsertId();
    }

    public function deleteException(int $id): void
    {
        $this->db->prepare('DELETE FROM opening_exceptions WHERE id = ?')->execute([$id]);
    }

    /** Ausnahme, die heute gilt (oder null) */
    public function todayException(?DateTimeImmutable $today = null): ?array
    {
        $t = ($today ?? new DateTimeImmutable('today'))->format('Y-m-d');
        foreach ($this->exceptions(true) as $e) {
            if ($e['date_from'] <= $t && $e['end'] >= $t) {
                return $e;
            }
        }
        return null;
    }

    /** Status für heute: ['closed' => bool, 'text' => string] */
    public function todayStatus(?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        if ($e = $this->todayException($today)) {
            return $e['type'] === 'closed'
                ? ['closed' => true, 'text' => 'Heute geschlossen' . ($e['title'] !== '' ? ' (' . $e['title'] . ')' : '')]
                : ['closed' => false, 'text' => 'Heute ' . self::span($e['open_time'], $e['close_time']) . ' geöffnet' . ($e['title'] !== '' ? ' (' . $e['title'] . ')' : '')];
        }
        $w = $this->week()[(int) $today->format('N')];
        return $w['closed']
            ? ['closed' => true, 'text' => 'Heute kein Verkauf']
            : ['closed' => false, 'text' => 'Heute ' . self::span($w['open'], $w['close']) . ' geöffnet'];
    }

    /**
     * Hinweise für „Aktuelles“: alle Ausnahmen, deren Vorankündigung begonnen hat
     * und die noch nicht vorbei sind. Gleiche Struktur wie News-Einträge.
     */
    public function announcements(?DateTimeImmutable $today = null): array
    {
        $t = ($today ?? new DateTimeImmutable('today'))->format('Y-m-d');
        $out = [];
        foreach ($this->exceptions(true) as $e) {
            if ($e['announce_days'] <= 0 || $e['announce_from'] > $t) {
                continue;
            }
            $out[] = [
                'id'           => 'oeffnungszeiten-' . $e['id'],
                'title'        => $e['headline'],
                'content'      => $e['text'],
                'image'        => '',
                'published_at' => $e['announce_from'],
                'notice'       => true,
                'when'         => $e['when'],
            ];
        }
        return $out;
    }

    /** Ergänzt berechnete Felder: Ende, Vorankündigungsdatum, Überschrift, Text */
    private function decorate(array $e): array
    {
        $e['announce_days'] = (int) $e['announce_days'];
        $e['end']  = $e['date_to'] !== '' ? $e['date_to'] : $e['date_from'];
        $from = new DateTimeImmutable($e['date_from']);
        $end  = new DateTimeImmutable($e['end']);
        $e['announce_from'] = $from->modify('-' . max(0, $e['announce_days']) . ' days')->format('Y-m-d');

        $single = $e['end'] === $e['date_from'];
        $e['when'] = $single
            ? self::DAYS[(int) $from->format('N')] . ', ' . $from->format('d.m.Y')
            : self::DAYS[(int) $from->format('N')] . ', ' . $from->format('d.m.') . ' bis '
              . self::DAYS[(int) $end->format('N')] . ', ' . $end->format('d.m.Y');

        $title = $e['title'] !== '' ? $e['title'] : ($single ? $from->format('d.m.Y') : 'vom ' . $from->format('d.m.') . ' bis ' . $end->format('d.m.Y'));
        $span  = self::span($e['open_time'], $e['close_time']);
        switch ($e['type']) {
            case 'closed':
                $e['headline'] = $title . ': geschlossen';
                $e['text'] = ($single ? 'Am ' : 'Von ') . $e['when'] . ' haben wir geschlossen.';
                $e['short'] = 'geschlossen';
                break;
            case 'open':
                $e['headline'] = $title . ': ausnahmsweise geöffnet';
                $e['text'] = ($single ? 'Am ' : 'Von ') . $e['when'] . ' haben wir ausnahmsweise für Sie geöffnet – ' . $span . '.';
                $e['short'] = 'geöffnet, ' . $span;
                break;
            default:
                $e['headline'] = $title . ': geänderte Öffnungszeiten';
                $e['text'] = ($single ? 'Am ' : 'Von ') . $e['when'] . ' gelten geänderte Öffnungszeiten: ' . $span . '.';
                $e['short'] = $span;
        }
        if (trim($e['note']) !== '') {
            $e['text'] .= "\n\n" . trim($e['note']);
        }
        return $e;
    }
}
