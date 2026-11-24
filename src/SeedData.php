<?php
declare(strict_types=1);

namespace App;

/**
 * Platzhalter-Startinhalte der Vorlage.
 * Werden nur beim allerersten Start in eine leere Datenbank geschrieben.
 */
final class SeedData
{
    public static function settings(): array
    {
        return [
            'site_name'        => 'Ihr Firmenname',
            'site_subtitle'    => 'Untertitel oder Zusatz',
            'slogan'           => 'Ein kurzer Slogan, der Ihr Angebot auf den Punkt bringt',
            'meta_description' => 'Ihr Firmenname – kurze Beschreibung für Suchmaschinen (ca. 150 Zeichen). Bitte im Backend unter Einstellungen anpassen.',
            'logo'             => '',
            'banner'           => '',
            'about_title'      => 'Herzlich willkommen',
            'about_text'       => "Hier steht ein kurzer Begrüßungstext. Stellen Sie sich und Ihr Angebot in wenigen Sätzen vor.\n\nAlle Texte, Bilder, Öffnungszeiten und Kontaktdaten lassen sich im Backend unter /admin/ ändern.\n\nWir freuen uns auf Sie!",

            'highlight_title'  => 'Unsere Highlights',
            'highlight_image'  => '',
            'highlight_text'   => <<<TXT
## Was uns ausmacht
In diesem Bereich stellen Sie etwas Besonderes ausführlicher vor: eine Spezialität, eine Geschichte oder das, wofür man Sie kennt.

## Zwischenüberschrift
Eine Zeile, die mit „## “ beginnt, wird zur Zwischenüberschrift. Eine Leerzeile beginnt einen neuen Absatz.
TXT,

            'menu_note'        => 'Alle Preise in Euro inkl. gesetzlicher MwSt.',
            'allergen_legend'  => "Allergene:\nA Gluten · B Krebstiere · C Eier · D Fisch · E Erdnüsse · F Soja · G Milch/Laktose · H Schalenfrüchte · I Sellerie · J Senf · K Sesam\n\nZusatzstoffe:\n1 mit Farbstoff · 2 mit Konservierungsstoffen · 3 mit Schwefeldioxid · 4 mit Geschmacksverstärker · 5 mit Antioxidationsmitteln",

            'address'          => "Musterstraße 1\n12345 Musterstadt",
            'phone'            => '01234 / 56 78 90',
            'fax'              => '',
            'email'            => 'info@ihre-domain.de',
            'opening_hours'    => '',
            'footer_text'      => 'Wir freuen uns auf Ihren Besuch!',
            'imprint_text'     => self::imprint(),
            'privacy_text'     => self::privacy(),
        ];
    }

    /**
     * [Kategorie, Beschreibung, Bereich ('menu'|'highlight'), Einträge]
     * Eintrag: [Name, Beschreibung, Preis (Cent), Kennzeichnung, Hinweise, Portion, Varianten [[Label, Cent], ...]]
     */
    public static function menu(): array
    {
        return [
            ['Highlight-Kategorie', 'Kategorien mit dem Bereich „Highlight-Bereich“ erscheinen hier', 'highlight', [
                ['Beispiel-Eintrag 1', 'Kurze Beschreibung', 1990, '', 'Empfehlung', '', []],
                ['Beispiel-Eintrag 2', 'Mit zwei Größen bzw. Preisvarianten', 2490, '', '', 'klein', [['groß', 3290]]],
            ]],
            ['Kategorie A', '', 'menu', [
                ['Beispiel-Eintrag', 'Kurze Beschreibung des Eintrags', 890, 'A, G', 'vegetarisch', '', []],
                ['Weiterer Eintrag', 'Kurze Beschreibung des Eintrags', 1290, '', '', '', []],
            ]],
            ['Kategorie B', 'Optionale Beschreibung der Kategorie', 'menu', [
                ['Beispiel-Eintrag', '', 450, '', '', '0,3 l', [['0,5 l', 650]]],
            ]],
        ];
    }

    /** Bearbeitbare Textblöcke des Kontakt-/Reservierungsformulars (Standardtexte) */
    public static function contactTexts(): array
    {
        return [
            'contact_title'       => 'Kontakt & Reservierung',
            'form_title'          => 'Anfrage & Tischreservierung',
            'form_intro'          => 'Sie haben eine Frage oder möchten reservieren? Nutzen Sie gerne dieses Formular – wir melden uns so schnell wie möglich bei Ihnen.',
            'form_res_note'       => 'Ihre Reservierung ist eine Anfrage und erst mit unserer Bestätigung verbindlich.',
            'form_consent'        => 'Ich stimme zu, dass meine Angaben zur Bearbeitung meiner Anfrage gespeichert und verarbeitet werden.',
            'form_hint'           => 'Kurzfristige Reservierungen gerne auch telefonisch unter {telefon}.',
            'form_success'        => 'Vielen Dank für Ihre Nachricht! Wir melden uns so schnell wie möglich.',
            'form_success_res'    => 'Vielen Dank für Ihre Reservierungsanfrage! Wir prüfen die Verfügbarkeit und melden uns so schnell wie möglich bei Ihnen. Die Reservierung ist erst mit unserer Bestätigung verbindlich.',
        ];
    }

    /** Version des mitgelieferten Impressums (für die automatische Aktualisierung) */
    public const IMPRINT_VERSION = 2;

    public static function imprint(): string
    {
        return <<<TXT
## Angaben gemäß § 5 DDG
[Firmenname und Rechtsform]
Musterstraße 1
12345 Musterstadt

## Vertreten durch
[Name der/des Vertretungsberechtigten]

## Kontakt
Telefon: 01234 / 56 78 90
E-Mail: info@ihre-domain.de

## Registereintrag
[Registergericht und Registernummer, falls vorhanden]

## Umsatzsteuer-ID
[USt-IdNr. gemäß § 27a UStG, falls vorhanden]

## Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV
[Name]
Musterstraße 1
12345 Musterstadt

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
[Firmenname und Rechtsform]
Musterstraße 1
12345 Musterstadt
Vertreten durch: [Name]
Telefon: 01234 / 56 78 90
E-Mail: info@ihre-domain.de

## 2. Allgemeines
Wir nehmen den Schutz Ihrer persönlichen Daten ernst und verarbeiten sie ausschließlich im Rahmen der gesetzlichen Bestimmungen, insbesondere der Datenschutz-Grundverordnung (DSGVO). Diese Webseite verwendet keine Analyse- oder Tracking-Werkzeuge, keine Werbe-Cookies und keine Social-Media-Plugins. Schriftarten und Skripte werden direkt von unserem Server geladen; es werden beim Aufruf der Seite keine Daten an Dritte wie Google übermittelt.

## 3. Hosting und Server-Logfiles
Diese Webseite wird bei [Hosting-Anbieter, Anschrift] betrieben. Beim Aufruf der Webseite werden durch den Webserver automatisch Informationen erfasst, die Ihr Browser übermittelt (sogenannte Server-Logfiles): IP-Adresse, Datum und Uhrzeit des Abrufs, aufgerufene Seite, Browsertyp und -version, Betriebssystem sowie die zuvor besuchte Seite (Referrer).
Diese Daten werden zur Sicherstellung eines störungsfreien Betriebs und zur Abwehr von Angriffen verarbeitet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO; unser berechtigtes Interesse liegt in der sicheren und stabilen Bereitstellung der Webseite. Mit dem Hosting-Anbieter besteht ein Vertrag zur Auftragsverarbeitung nach Art. 28 DSGVO.

## 4. Verschlüsselung
Diese Webseite nutzt aus Sicherheitsgründen eine SSL- bzw. TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie an „https://“ in der Adresszeile Ihres Browsers. Daten, die Sie über das Kontaktformular senden, können so nicht von Dritten mitgelesen werden.

## 5. Cookies
Wir verwenden ausschließlich ein technisch notwendiges Cookie („site_session“). Es speichert eine zufällige Sitzungskennung, die für den Schutz des Kontaktformulars vor Missbrauch (Sicherheits-Token, Spam-Schutz) und für die Anmeldung im Verwaltungsbereich erforderlich ist. Das Cookie wird beim Schließen des Browsers gelöscht. Rechtsgrundlage ist § 25 Abs. 2 Nr. 2 TDDDG in Verbindung mit Art. 6 Abs. 1 lit. f DSGVO. Eine Einwilligung ist hierfür nicht erforderlich.

## 6. Kontaktformular und Reservierungsanfragen
Wenn Sie uns über das Formular eine Anfrage oder eine Reservierungsanfrage senden, verarbeiten wir die von Ihnen eingegebenen Daten: Name, E-Mail-Adresse, optional Telefonnummer, Betreff und Nachricht sowie bei Reservierungen Datum, Uhrzeit, Personenzahl und den gewünschten Bereich.
Wir verwenden diese Daten ausschließlich, um Ihre Anfrage zu bearbeiten, die Verfügbarkeit zu prüfen und Ihnen zu antworten. Die Anfrage wird in unserer Datenbank auf dem Server unseres Hosting-Anbieters gespeichert und uns zusätzlich per E-Mail zugestellt.
Rechtsgrundlage ist bei Reservierungsanfragen Art. 6 Abs. 1 lit. b DSGVO (Durchführung vorvertraglicher Maßnahmen), bei sonstigen Anfragen Ihre Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO sowie unser berechtigtes Interesse an der Beantwortung von Anfragen (Art. 6 Abs. 1 lit. f DSGVO). Ihre Einwilligung können Sie jederzeit mit Wirkung für die Zukunft widerrufen, etwa per E-Mail.
Wir löschen Ihre Daten, sobald sie für die Bearbeitung Ihrer Anfrage nicht mehr erforderlich sind und keine gesetzlichen Aufbewahrungspflichten entgegenstehen.

## 7. Kontakt per E-Mail oder Telefon
Wenn Sie uns per E-Mail oder Telefon kontaktieren, verarbeiten wir Ihre Angaben zur Bearbeitung Ihres Anliegens. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b bzw. lit. f DSGVO.

## 8. Externe Links
Unsere Webseite enthält Links zu anderen Anbietern, etwa „Route planen“ zu OpenStreetMap. Daten werden an den jeweiligen Anbieter erst übermittelt, wenn Sie den Link anklicken. Für die Datenverarbeitung auf diesen Seiten ist der jeweilige Anbieter verantwortlich.

## 9. Ihre Rechte
Sie haben jederzeit das Recht auf Auskunft über Ihre bei uns gespeicherten Daten (Art. 15 DSGVO), auf Berichtigung (Art. 16 DSGVO), Löschung (Art. 17 DSGVO), Einschränkung der Verarbeitung (Art. 18 DSGVO), Datenübertragbarkeit (Art. 20 DSGVO) sowie auf Widerspruch gegen die Verarbeitung (Art. 21 DSGVO). Wenden Sie sich dazu einfach an die oben genannten Kontaktdaten.
Sie haben außerdem das Recht, sich bei einer Datenschutz-Aufsichtsbehörde zu beschweren. Zuständig für uns ist: [Datenschutz-Aufsichtsbehörde des Bundeslandes, Anschrift].

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
        return [];
    }

    public static function news(): array
    {
        return [
            ['Unsere neue Webseite ist online', "Herzlich willkommen auf unserer neuen Webseite! Hier finden Sie ab sofort unser Angebot, Neuigkeiten und alle Kontaktdaten.\n\nÜber das Kontaktformular können Sie uns jederzeit eine Nachricht schicken.", 0],
        ];
    }
}
