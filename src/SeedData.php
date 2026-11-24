<?php
declare(strict_types=1);

namespace App;

/**
 * Startinhalte für „Der Hunsrück-Imker“ (Texte der bisherigen Webseite hunsrueck-imker.de
 * und des Verkaufsschilds „Honigsorten 2026“). Werden nur beim allerersten Start
 * in eine leere Datenbank geschrieben und danach im Backend gepflegt.
 */
final class SeedData
{
    public static function settings(): array
    {
        return [
            'site_name'        => 'Der Hunsrück-Imker',
            'site_subtitle'    => 'Imkerei Mark Marquis',
            'slogan'           => 'Honig aus der Nationalparkregion',
            'meta_description' => 'Der Hunsrück-Imker Mark Marquis aus Herborn: Honig aus der Nationalparkregion Hunsrück-Hochwald – Frühjahrsblütenhonig, Sommertrachthonig sowie Wald- und Blütenhonig direkt vom Imker.',
            'logo'             => '',
            'banner'           => '',
            'about_title'      => 'Herzlich willkommen beim Hunsrück-Imker!',
            'about_text'       => "Am östlichen Rand des Hunsrücks, zwischen Wildenburgkopf und Idar-Oberstein gelegen, imkere ich mit meinen 25 Bienenvölkern im schönen Hunsrückort Herborn. Die Ortschaft grenzt im Südwesten mit ihrer Gemarkung direkt an den Nationalpark. Diesen besuchen meine Bienen regelmäßig, da sie nur wenige hundert Meter entfernt in ihren Stöcken leben.\n\nWeitere Völker stehen in Weierbach, Algenrodt und Kirchenbollenbach. Durch die unterschiedlichen Standorte bekommt jeder Honig seine ganz eigenen Geschmacksnuancen.",

            'highlight_title'  => 'Die Trachten rund um Herborn',
            'highlight_image'  => '',
            'highlight_text'   => <<<TXT
## Frühjahr
Die Umgebung ist geprägt von viel Wald und Wiesen und eher extensiver Landwirtschaft. Im Frühjahr gibt es immer wieder wechselnde Rapsfelder in der Umgebung, die für einen sehr hellen Frühjahrsblütenhonig sorgen – ergänzt um Steinobst wie Kirsche, Mirabelle und Zwetschge. Der vielfach vorhandene Schwarzdorn ist ein erster Pollen- und Nektarlieferant. Später folgt die Apfelblüte in den vielen Privatgärten.

Den Abschluss der Frühjahrstracht bildet bei uns der Weißdorn, der im Mai in den Ortsrandlagen und vielfach in den Wäldern der „Heckengesellschaften“ von Veitsrodt und Herborn blüht.

## Sommer
Im Sommer blühen die vielen Linden, besonders in Richtung des Nachbardorfs Veitsrodt. Auf nahezu jeder Lichtung und durchforsteten Fläche in den umgebenden Wäldern findet man zahlreiche Brombeerhecken. Diese liefern neben wilden Himbeeren viel Nektar für den Sommerhonig.

Je nach Jahr sammeln die Bienen außerdem Honigtau von den Bäumen im nahen Wald – daraus entsteht unser Wald- und Blütenhonig.
TXT,

            'menu_title'       => 'Unsere Honige',
            'menu_note'        => "Alle Honige im 500-g-Glas. Ganze Kiste (12 Gläser): 3 € Rabatt.\n\nAlle Preise in Euro.",
            'allergen_legend'  => '',

            'address'          => "Hauptstraße 11\n55758 Herborn",
            'phone'            => '0162 9413598',
            'fax'              => '',
            'email'            => 'mail@hunsrueck-imker.de',
            'opening_hours'    => '',
            'footer_text'      => 'Honig aus der Nationalparkregion – direkt vom Imker.',
            'imprint_text'     => self::imprint(),
            'privacy_text'     => self::privacy(),
        ];
    }

    /**
     * [Kategorie, Beschreibung, Bereich ('menu'|'highlight'), Einträge]
     * Eintrag: [Name, Beschreibung, Preis (Cent, 0 = kein Preis), Kennzeichnung, Hinweise, Portion, Varianten [[Label, Cent], ...], Foto]
     */
    public static function menu(): array
    {
        return [
            ['Honigsorten 2026', 'Jede Ortslage mit eigenem Geschmack', 'menu', [
                ['Sommertrachthonig', 'Wald, Brombeere, Himbeere, Lindenblüte – aus Herborn, Kirchenbollenbach oder Weierbach', 700, '', 'cremig', '500 g', [], 'assets/img/honig/sommertrachthonig.jpg'],
                ['Wald- und Blütenhonig', 'Waldhonig, Lindenblüten, Brombeere – aus Herborn', 700, '', 'flüssig', '500 g', [], 'assets/img/honig/wald-und-bluetenhonig.jpg'],
                ['Frühjahrsblütenhonig', 'Viel Obstblüte und gemischte Frühjahrsblüte – aus Herborn oder Kirchenbollenbach', 650, '', 'feinsteif', '500 g', [], 'assets/img/honig/fruehjahrsbluetenhonig.jpg'],
                ['Frühjahrsblütenhonig mit Raps', 'Überwiegend Raps – aus Weierbach', 650, '', 'feinsteif', '500 g', [], ''],
            ]],
        ];
    }

    /** Bearbeitbare Textblöcke des Kontakt-/Bestellformulars (Standardtexte) */
    public static function contactTexts(): array
    {
        return [
            'contact_title'       => 'Kontakt & Bestellung',
            'form_title'          => 'Anfrage & Honigbestellung',
            'form_intro'          => 'Sie möchten Honig bestellen oder haben eine Frage? Schreiben Sie mir gerne – ich melde mich so schnell wie möglich.',
            'form_res_note'       => 'Ihre Bestellung ist unverbindlich. Ich prüfe, ob alles vorrätig ist, und melde mich mit einer Bestätigung bei Ihnen.',
            'form_consent'        => 'Ich stimme zu, dass meine Angaben zur Bearbeitung meiner Anfrage gespeichert und verarbeitet werden.',
            'form_hint'           => 'Gerne auch per WhatsApp oder Telefon: {telefon}.',
            'form_success'        => 'Vielen Dank für Ihre Nachricht! Ich melde mich so schnell wie möglich.',
            'form_success_res'    => 'Vielen Dank für Ihre Bestellung! Ich prüfe, ob alles vorrätig ist, und melde mich so schnell wie möglich bei Ihnen.',
        ];
    }

    /** Version des mitgelieferten Impressums (für die automatische Aktualisierung) */
    public const IMPRINT_VERSION = 2;

    public static function imprint(): string
    {
        return <<<TXT
## Angaben gemäß § 5 DDG
Imkerei „Der Hunsrück-Imker“
Mark Marquis
Hauptstraße 11
55758 Herborn

## Kontakt
Telefon: 0162 9413598
E-Mail: mail@hunsrueck-imker.de

## Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV
Mark Marquis
Hauptstraße 11
55758 Herborn

## Verbraucherstreitbeilegung
Wir sind nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.
TXT;
    }

    /** Version der mitgelieferten Datenschutzerklärung (für die automatische Aktualisierung) */
    public const PRIVACY_VERSION = 2;

    public static function privacy(): string
    {
        return <<<TXT
## 1. Verantwortlicher
Verantwortlich für die Datenverarbeitung auf dieser Webseite ist:
Imkerei „Der Hunsrück-Imker“, Mark Marquis
Hauptstraße 11
55758 Herborn
Telefon: 0162 9413598
E-Mail: mail@hunsrueck-imker.de

## 2. Allgemeines
Wir nehmen den Schutz Ihrer persönlichen Daten ernst und verarbeiten sie ausschließlich im Rahmen der gesetzlichen Bestimmungen, insbesondere der Datenschutz-Grundverordnung (DSGVO). Diese Webseite verwendet keine Analyse- oder Tracking-Werkzeuge, keine Werbe-Cookies und keine Social-Media-Plugins. Schriftarten und Skripte werden direkt von unserem Server geladen; es werden beim Aufruf der Seite keine Daten an Dritte wie Google übermittelt.

## 3. Hosting und Server-Logfiles
Diese Webseite wird bei [Hosting-Anbieter, Anschrift] betrieben. Beim Aufruf der Webseite werden durch den Webserver automatisch Informationen erfasst, die Ihr Browser übermittelt (sogenannte Server-Logfiles): IP-Adresse, Datum und Uhrzeit des Abrufs, aufgerufene Seite, Browsertyp und -version, Betriebssystem sowie die zuvor besuchte Seite (Referrer).
Diese Daten werden zur Sicherstellung eines störungsfreien Betriebs und zur Abwehr von Angriffen verarbeitet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO; unser berechtigtes Interesse liegt in der sicheren und stabilen Bereitstellung der Webseite. Mit dem Hosting-Anbieter besteht ein Vertrag zur Auftragsverarbeitung nach Art. 28 DSGVO.

## 4. Verschlüsselung
Diese Webseite nutzt aus Sicherheitsgründen eine SSL- bzw. TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie an „https://“ in der Adresszeile Ihres Browsers. Daten, die Sie über das Kontaktformular senden, können so nicht von Dritten mitgelesen werden.

## 5. Cookies
Wir verwenden ausschließlich ein technisch notwendiges Cookie („site_session“). Es speichert eine zufällige Sitzungskennung, die für den Schutz des Kontaktformulars vor Missbrauch (Sicherheits-Token, Spam-Schutz) und für die Anmeldung im Verwaltungsbereich erforderlich ist. Das Cookie wird beim Schließen des Browsers gelöscht. Rechtsgrundlage ist § 25 Abs. 2 Nr. 2 TDDDG in Verbindung mit Art. 6 Abs. 1 lit. f DSGVO. Eine Einwilligung ist hierfür nicht erforderlich.

## 6. Kontaktformular und Bestellanfragen
Wenn Sie uns über das Formular eine Anfrage oder eine Honigbestellung senden, verarbeiten wir die von Ihnen eingegebenen Daten: Name, E-Mail-Adresse, optional Telefonnummer, Betreff und Nachricht sowie bei Bestellungen die gewünschte Lieferart, die Anzahl der Gläser und einen Wunschtermin.
Wir verwenden diese Daten ausschließlich, um Ihre Anfrage bzw. Bestellung zu bearbeiten und Ihnen zu antworten. Die Anfrage wird in unserer Datenbank auf dem Server unseres Hosting-Anbieters gespeichert und uns zusätzlich per E-Mail zugestellt.
Rechtsgrundlage ist bei Bestellungen Art. 6 Abs. 1 lit. b DSGVO (Durchführung vorvertraglicher Maßnahmen), bei sonstigen Anfragen Ihre Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO sowie unser berechtigtes Interesse an der Beantwortung von Anfragen (Art. 6 Abs. 1 lit. f DSGVO). Ihre Einwilligung können Sie jederzeit mit Wirkung für die Zukunft widerrufen, etwa per E-Mail.
Wir löschen Ihre Daten, sobald sie für die Bearbeitung Ihrer Anfrage nicht mehr erforderlich sind und keine gesetzlichen Aufbewahrungspflichten entgegenstehen.

## 7. Kontakt per E-Mail oder Telefon
Wenn Sie uns per E-Mail oder Telefon kontaktieren, verarbeiten wir Ihre Angaben zur Bearbeitung Ihres Anliegens. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b bzw. lit. f DSGVO.

## 8. Externe Links
Unsere Webseite enthält Links zu anderen Anbietern, etwa „Route planen“ zu OpenStreetMap. Daten werden an den jeweiligen Anbieter erst übermittelt, wenn Sie den Link anklicken. Für die Datenverarbeitung auf diesen Seiten ist der jeweilige Anbieter verantwortlich.

## 9. Ihre Rechte
Sie haben jederzeit das Recht auf Auskunft über Ihre bei uns gespeicherten Daten (Art. 15 DSGVO), auf Berichtigung (Art. 16 DSGVO), Löschung (Art. 17 DSGVO), Einschränkung der Verarbeitung (Art. 18 DSGVO), Datenübertragbarkeit (Art. 20 DSGVO) sowie auf Widerspruch gegen die Verarbeitung (Art. 21 DSGVO). Wenden Sie sich dazu einfach an die oben genannten Kontaktdaten.
Sie haben außerdem das Recht, sich bei einer Datenschutz-Aufsichtsbehörde zu beschweren. Zuständig für uns ist: Der Landesbeauftragte für den Datenschutz und die Informationsfreiheit Rheinland-Pfalz, Hintere Bleiche 34, 55116 Mainz.

## 10. Stand
Stand dieser Datenschutzerklärung: November 2026
TXT;
    }

    /** Version der Start-Bannerbilder. Erhöhen, wenn die Liste unten ersetzt werden soll. */
    public const SLIDES_VERSION = 1;

    /**
     * Start-Bannerbilder: [Pfad relativ zu public/, Beschreibung, Bildausschnitt]
     * z. B. ['assets/img/slides/bild-1.jpg', 'Außenansicht', 'center']
     * Bilder lassen sich auch im Backend unter „Bannerbilder“ hochladen.
     */
    public static function slides(): array
    {
        return [
            ['assets/img/slides/rapsfelder-herborn.jpg', 'Rapsfelder in Herborn', 'center'],
            ['assets/img/slides/honigauswahl.jpg', 'Honigauswahl vom Hunsrück-Imker', 'center'],
            ['assets/img/slides/krokusbluete.jpg', 'Krokusblüte – erster Pollen im Frühjahr', 'center'],
            ['assets/img/slides/inkarnatklee.jpg', 'Inkarnatklee und Kornblumen', 'center'],
        ];
    }

    public static function news(): array
    {
        return [
            ['Die Honige 2026 sind im Glas', "Der neue Frühjahrsblütenhonig von 2026 ist im Glas! Geschleudert wurde Ende Mai 2026 – von meinen Völkern aus Herborn, Weierbach, Algenrodt und Kirchenbollenbach. In diesem Jahr mit geringerem Anteil Rapsnektar. Die Honige der einzelnen Ortslagen haben durch Steinobst, Kernobst, Weißdorn, Ahorn und weitere Nektarherkünfte ihre ganz eigenen Geschmacksnuancen; etwas mehr Rapsnektar hat der Honig aus Weierbach.\n\nAuch der cremige Sommertrachthonig und der flüssige Wald- und Blütenhonig sind erhältlich – alle Sorten und Preise finden Sie unter „Unsere Honige“.", 0],
            ['Wald- und Blütenhonig – flüssig abgefüllt', "Direkt nach der Frühtracht 2026 haben die Bienen zunächst viel Honigtau von den Bäumen im nahen Wald gesammelt. Im Juni kam Nektar von Linde und Brombeere hinzu. So entstand ein Wald- und Blütenhonig, der Mitte Juli reif geerntet und flüssig ins Glas abgefüllt wurde.", 3],
        ];
    }
}
