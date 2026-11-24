<?php
declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Versand von Benachrichtigungen. Mit hinterlegten SMTP-Zugangsdaten wird
 * über den Mailserver des Postfachs verschickt (empfohlen, z. B. IONOS),
 * sonst über die PHP-Funktion mail().
 */
final class Mailer
{
    public const SECURE_OPTIONS = [
        'tls' => 'STARTTLS (Port 587, empfohlen)',
        'ssl' => 'SSL/TLS (Port 465)',
        ''    => 'Keine Verschlüsselung',
    ];

    public function __construct(private readonly App $app) {}

    public function usesSmtp(): bool
    {
        $s = $this->app->settings;
        return $s->get('smtp_host') !== '' && $s->get('smtp_user') !== '' && $s->get('smtp_pass') !== '';
    }

    /**
     * @throws RuntimeException mit verständlicher Fehlermeldung
     */
    public function send(string $to, string $subject, string $body, ?string $replyTo = null, string $replyName = ''): void
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Keine gültige Empfängeradresse eingestellt.');
        }
        $s    = $this->app->settings;
        $from = ContactForm::sender($this->app);

        $mail = new PHPMailer(true);
        $mail->CharSet  = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;
        $mail->Timeout  = 15;

        try {
            if ($this->usesSmtp()) {
                $mail->isSMTP();
                // Nicht minutenlang auf einen hängenden Mailserver warten (Standard wären 300 s)
                $mail->getSMTPInstance()->Timelimit = 15;
                $mail->Host       = $s->get('smtp_host');
                $mail->Port       = (int) ($s->get('smtp_port') ?: 587);
                $mail->SMTPAuth   = true;
                $mail->Username   = $s->get('smtp_user');
                $mail->Password   = $s->get('smtp_pass');
                $secure           = $s->get('smtp_secure', 'tls');
                $mail->SMTPSecure = match ($secure) {
                    'ssl'   => PHPMailer::ENCRYPTION_SMTPS,
                    ''      => '',
                    default => PHPMailer::ENCRYPTION_STARTTLS,
                };
                $mail->SMTPAutoTLS = $secure !== '';
                // Bei SMTP muss der Absender in der Regel das angemeldete Postfach sein
                if (filter_var($mail->Username, FILTER_VALIDATE_EMAIL) && $s->get('contact_email_from') === '') {
                    $from = $mail->Username;
                }
            } else {
                $mail->isMail();
                $mail->Sender = $from; // Envelope-Absender (-f)
            }

            $mail->setFrom($from, $s->get('site_name', 'Webseite'));
            $mail->addAddress($to);
            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo, $replyName);
            }
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
        } catch (MailException $e) {
            throw new RuntimeException(self::explain($mail->ErrorInfo ?: $e->getMessage()));
        }
    }

    /** Technische Fehlermeldungen in verständliche Hinweise übersetzen. */
    private static function explain(string $error): string
    {
        $hint = '';
        if (stripos($error, 'authenticate') !== false || stripos($error, '535') !== false) {
            $hint = ' – Benutzername oder Passwort des Postfachs ist falsch.';
        } elseif (stripos($error, 'connect') !== false) {
            $hint = ' – Der Mailserver ist nicht erreichbar. Servername, Port und Verschlüsselung prüfen.';
        } elseif (stripos($error, 'mail function') !== false || stripos($error, 'Could not instantiate') !== false) {
            $hint = ' – Der Server verschickt ohne SMTP-Zugangsdaten keine Mails. Bitte SMTP einrichten.';
        } elseif (stripos($error, 'sender') !== false || stripos($error, '553') !== false || stripos($error, '550') !== false) {
            $hint = ' – Der Mailserver akzeptiert die Absenderadresse nicht. Als Absender das angemeldete Postfach verwenden.';
        }
        return 'E-Mail konnte nicht gesendet werden: ' . $error . $hint;
    }
}
