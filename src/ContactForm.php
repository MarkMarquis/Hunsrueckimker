<?php
declare(strict_types=1);

namespace App;

/**
 * Validierung, Spam-Schutz (Honeypot + Mindestausfüllzeit), Speicherung
 * und optionale E-Mail-Benachrichtigung für das Kontaktformular.
 */
final class ContactForm
{
    private const MIN_SECONDS = 3;

    public const TYPES = [
        'anfrage'      => 'Allgemeine Anfrage',
        'reservierung' => 'Tischreservierung',
    ];

    /** Sitzbereiche für Reservierungen: Schlüssel => [Bezeichnung, Mindestpersonen] */
    public const AREAS = [
        'egal'           => ['Keine Präferenz', 0],
        'innen'          => ['Innenbereich', 0],
        'aussen'         => ['Außenbereich', 0],
    ];

    public const MAX_PERSONS = 200;

    public static function areaLabel(string $key): string
    {
        return self::AREAS[$key][0] ?? '';
    }

    public static function weekday(string $date): string
    {
        $ts = strtotime($date);
        return $ts ? ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'][(int) date('w', $ts)] : '';
    }

    public function __construct(private readonly App $app) {}

    /** @return array{ok: bool, errors: array<string,string>, message: string} */
    public function handle(array $input): array
    {
        $data = [
            'name'    => trim((string) ($input['name'] ?? '')),
            'email'   => trim((string) ($input['email'] ?? '')),
            'phone'   => trim((string) ($input['phone'] ?? '')),
            'subject' => trim((string) ($input['subject'] ?? '')),
            'message' => trim((string) ($input['message'] ?? '')),
            'type'    => ($input['type'] ?? '') === 'reservierung' ? 'reservierung' : 'anfrage',
            'res_date' => '',
            'res_time' => '',
            'persons'  => 0,
            'area'     => '',
        ];
        $isReservation = $data['type'] === 'reservierung';
        if ($isReservation) {
            $data['res_date'] = trim((string) ($input['res_date'] ?? ''));
            $data['res_time'] = trim((string) ($input['res_time'] ?? ''));
            $data['persons']  = (int) ($input['persons'] ?? 0);
            $data['area']     = array_key_exists((string) ($input['area'] ?? ''), self::AREAS) ? (string) $input['area'] : 'egal';
        }

        // Spam: verstecktes Feld ausgefüllt oder Formular zu schnell abgeschickt
        $startedAt = (int) ($_SESSION['contact_form_started'] ?? 0);
        $tooFast   = $startedAt === 0 || (time() - $startedAt) < self::MIN_SECONDS;
        if (($input['website'] ?? '') !== '' || $tooFast) {
            // Bots bekommen eine scheinbare Erfolgsmeldung
            return ['ok' => true, 'errors' => [], 'message' => 'Vielen Dank für Ihre Nachricht!'];
        }

        $errors = [];
        if ($data['name'] === '' || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Bitte geben Sie Ihren Namen an.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 200) {
            $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
        }
        if (mb_strlen($data['phone']) > 50 || preg_match('/[^0-9+\/()\- ]/', $data['phone'])) {
            $errors['phone'] = 'Bitte prüfen Sie die Telefonnummer.';
        }
        if (mb_strlen($data['subject']) > 150) {
            $errors['subject'] = 'Der Betreff ist zu lang.';
        }
        if ($isReservation) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $data['res_date']);
            if (!$date || $date->format('Y-m-d') !== $data['res_date']) {
                $errors['res_date'] = 'Bitte wählen Sie ein Datum.';
            } elseif ($date < new \DateTimeImmutable('today')) {
                $errors['res_date'] = 'Das Datum liegt in der Vergangenheit.';
            } elseif ($date > new \DateTimeImmutable('+1 year')) {
                $errors['res_date'] = 'Reservierungen sind bis zu ein Jahr im Voraus möglich.';
            }
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $data['res_time'])) {
                $errors['res_time'] = 'Bitte wählen Sie eine Uhrzeit.';
            }
            if ($data['persons'] < 1 || $data['persons'] > self::MAX_PERSONS) {
                $errors['persons'] = 'Bitte geben Sie die Anzahl der Personen an (1 bis ' . self::MAX_PERSONS . ').';
            } elseif ($data['persons'] < self::AREAS[$data['area']][1]) {
                $errors['area'] = 'Dieser Bereich ist erst für Gruppen ab ' . self::AREAS[$data['area']][1] . ' Personen buchbar.';
            }
            if (mb_strlen($data['message']) > 5000) {
                $errors['message'] = 'Die Anmerkung ist zu lang (max. 5000 Zeichen).';
            }
            if ($data['subject'] === '' && !isset($errors['res_date']) && !isset($errors['res_time']) && !isset($errors['persons'])) {
                $data['subject'] = 'Reservierungsanfrage ' . Format::date($data['res_date'], 'd.m.Y') . ', '
                    . $data['res_time'] . ' Uhr, ' . $data['persons'] . ' ' . ($data['persons'] === 1 ? 'Person' : 'Personen');
            }
        } elseif (mb_strlen($data['message']) < 10 || mb_strlen($data['message']) > 5000) {
            $errors['message'] = 'Bitte schreiben Sie eine Nachricht (10 bis 5000 Zeichen).';
        }
        if (empty($input['privacy'])) {
            $errors['privacy'] = 'Bitte stimmen Sie der Verarbeitung Ihrer Daten zu.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'message' => 'Bitte prüfen Sie Ihre Eingaben.'];
        }

        $id = $this->app->messages->create($data);
        $mailError = $this->notify($data);
        if ($mailError !== '') {
            $this->app->messages->setMailError($id, $mailError);
        }
        unset($_SESSION['contact_form_started']);

        $defaults = SeedData::contactTexts();
        $key = $isReservation ? 'form_success_res' : 'form_success';
        $message = trim($this->app->settings->get($key)) ?: $defaults[$key];
        return ['ok' => true, 'errors' => [], 'message' => $message];
    }

    /** Empfänger: Backend-Einstellung, sonst config.local.php. Leer = keine E-Mail. */
    public static function recipient(App $app): string
    {
        $to = trim($app->settings->get('contact_email_to')) ?: trim((string) $app->config['mail_to']);
        return filter_var($to, FILTER_VALIDATE_EMAIL) ? $to : '';
    }

    /** Absender: Backend-Einstellung, sonst Konfiguration, sonst noreply@<Domain der Webseite>. */
    public static function sender(App $app): string
    {
        foreach ([$app->settings->get('contact_email_from'), (string) $app->config['mail_from']] as $from) {
            $from = trim($from);
            if ($from !== '' && $from !== 'noreply@localhost' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
                return $from;
            }
        }
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')));
        $host = preg_replace('/^(www|neu)\./', '', $host);
        return 'noreply@' . (preg_match('/^[a-z0-9.-]+$/', $host) ? $host : 'localhost');
    }

    /** Benachrichtigung verschicken. Gibt eine Fehlermeldung zurück oder '' bei Erfolg / ohne Empfänger. */
    private function notify(array $d): string
    {
        $to = self::recipient($this->app);
        if ($to === '') {
            return '';
        }
        $isReservation = $d['type'] === 'reservierung';
        $subject = ($isReservation ? 'Reservierungsanfrage: ' : 'Kontaktanfrage: ')
                 . ($d['subject'] !== '' ? $d['subject'] : 'Webseite');
        $body    = ($isReservation
                    ? "Neue RESERVIERUNGSANFRAGE über die Webseite\n"
                      . "Bitte Verfügbarkeit prüfen und dem Gast antworten.\n\n"
                      . 'Datum:    ' . Format::date($d['res_date'], 'd.m.Y') . ' (' . self::weekday($d['res_date']) . ")\n"
                      . "Uhrzeit:  {$d['res_time']} Uhr\n"
                      . "Personen: {$d['persons']}\n"
                      . 'Bereich:  ' . self::areaLabel($d['area']) . "\n\n"
                    : "Neue Nachricht über das Kontaktformular der Webseite\n\n")
                 . "Name:    {$d['name']}\nE-Mail:  {$d['email']}\nTelefon: " . ($d['phone'] !== '' ? $d['phone'] : '–') . "\n"
                 . "Betreff: {$d['subject']}\n\n"
                 . ($d['message'] !== '' ? ($isReservation ? "Anmerkungen:\n" : '') . "{$d['message']}\n\n" : '')
                 . "--\nZum Antworten einfach auf diese E-Mail antworten – die Antwort geht direkt an {$d['email']}.\n";
        try {
            (new Mailer($this->app))->send($to, str_replace(["\r", "\n"], ' ', $subject), $body, $d['email'], $d['name']);
            return '';
        } catch (\RuntimeException $e) {
            if (function_exists('app_log_error')) {
                app_log_error(APP_ROOT, 'Kontaktformular: ' . $e->getMessage());
            }
            return $e->getMessage();
        }
    }
}
