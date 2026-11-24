{extends file="admin/layout.tpl"}
{block name=title}Einstellungen{/block}
{block name=content}
<h1>Einstellungen</h1>
<form method="post" action="index.php?page=settings" enctype="multipart/form-data" class="form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">

    <section class="card">
        <h2>Allgemein</h2>
        <div class="grid-2">
            <label class="field">Name
                <input type="text" name="site_name" value="{$site.site_name}" required>
            </label>
            <label class="field">Untertitel
                <input type="text" name="site_subtitle" value="{$site.site_subtitle|default:''}">
            </label>
        </div>
        <label class="field">Slogan (im Banner)
            <input type="text" name="slogan" value="{$site.slogan}">
        </label>
        <label class="field">Beschreibung für Suchmaschinen
            <textarea name="meta_description" rows="2" maxlength="300">{$site.meta_description}</textarea>
        </label>
    </section>

    <section class="card" id="bilder">
        <h2>Logo &amp; Bilder</h2>
        <div class="grid-3">
            {foreach $images as $key => $img}
            <div class="field">
                <span class="field-label">{$img.label}</span>
                <div class="image-preview" data-preview="{$key}">
                    {if $img.url}<img src="{$img.url}" alt="{$img.label}">{else}<span class="muted small">Kein Bild</span>{/if}
                </div>
                <input type="file" name="{$key}" accept="image/jpeg,image/png,image/webp,image/gif" data-preview-target="{$key}">
                {if $site.$key}
                    <label class="check"><input type="checkbox" name="remove_{$key}" value="1"> Hochgeladenes Bild entfernen (Standardbild verwenden)</label>
                {/if}
                <small class="muted">{$img.hint}</small>
            </div>
            {/foreach}
        </div>
    </section>

    <section class="card">
        <h2>Banner</h2>
        <label class="check"><input type="checkbox" name="hero_news" value="1"{if $hero_news} checked{/if}>
            Neueste Meldung aus „Aktuelles“ im Banner anzeigen</label>
        <p class="muted small">Es wird automatisch die neueste veröffentlichte Meldung gezeigt. Der Bereich „Aktuelles“ weiter unten bleibt zusätzlich bestehen.</p>
    </section>

    <section class="card">
        <h2>Aktuelles – Willkommenstext</h2>
        <label class="field">Überschrift
            <input type="text" name="about_title" value="{$site.about_title}">
        </label>
        <label class="field">Text <small>(Leerzeile = neuer Absatz)</small>
            <textarea name="about_text" rows="8">{$site.about_text}</textarea>
        </label>
    </section>

    <section class="card">
        <h2>Highlight-Bereich</h2>
        <p class="muted small">Die Sorten in diesem Bereich pflegen Sie unter <a href="index.php?page=menu">Honigsorten</a> (Kategorien mit Bereich „Highlight-Bereich“; meist leer lassen).</p>
        <label class="field">Überschrift
            <input type="text" name="highlight_title" value="{$site.highlight_title|default:''}">
        </label>
        <label class="field">Text <small>(Leerzeile = neuer Absatz, „## Titel“ = Zwischenüberschrift)</small>
            <textarea name="highlight_text" rows="16">{$site.highlight_text|default:''}</textarea>
        </label>
    </section>

    <section class="card">
        <h2>Honigsorten</h2>
        <label class="field">Überschrift des Bereichs
            <input type="text" name="menu_title" value="{$site.menu_title|default:''}" maxlength="100" placeholder="Unsere Honige">
        </label>
        <label class="field">Hinweis unter den Sorten <small>(z. B. Glasgröße, Rabatt, Preisangabe)</small>
            <textarea name="menu_note" rows="4">{$site.menu_note|default:''}</textarea>
        </label>
        <label class="field">Legende Kennzeichnungen <small>(leer = ausgeblendet)</small>
            <textarea name="allergen_legend" rows="7">{$site.allergen_legend|default:''}</textarea>
        </label>
    </section>

    <section class="card">
        <h2>Kontakt</h2>
        <div class="grid-2">
            <label class="field">Adresse <small>(mehrzeilig)</small>
                <textarea name="address" rows="3">{$site.address}</textarea>
            </label>
            <div class="field">
                <span class="field-label">Öffnungszeiten</span>
                <p class="muted small">Werden jetzt unter <a href="index.php?page=hours">Öffnungszeiten</a> gepflegt – mit Wochentagen und Ausnahmen (z. B. Feiertage).</p>
            </div>
            <label class="field">Telefon
                <input type="text" name="phone" value="{$site.phone}">
            </label>
            <label class="field">Telefax
                <input type="text" name="fax" value="{$site.fax|default:''}">
            </label>
            <label class="field">E-Mail
                <input type="email" name="email" value="{$site.email}">
            </label>
        </div>
        <label class="field">Text in der Fußzeile
            <input type="text" name="footer_text" value="{$site.footer_text}">
        </label>
    </section>

    <section class="card" id="formulartexte">
        <h2>Texte im Kontakt- &amp; Bestellformular</h2>
        <p class="muted small">Leere Felder werden auf der Webseite ausgeblendet (außer Überschriften und Einwilligung – dort wird dann der Standardtext verwendet).</p>
        <div class="grid-2">
            <label class="field">Überschrift des Bereichs
                <input type="text" name="contact_title" value="{$site.contact_title|default:''}" maxlength="100">
            </label>
            <label class="field">Überschrift des Formulars
                <input type="text" name="form_title" value="{$site.form_title|default:''}" maxlength="100">
            </label>
        </div>
        <label class="field">Einleitungstext über dem Formular
            <textarea name="form_intro" rows="2" maxlength="500">{$site.form_intro|default:''}</textarea>
        </label>
        <label class="field">Hinweis bei Bestellungen <small>(erscheint unter Lieferart, Anzahl und Wunschtermin)</small>
            <textarea name="form_res_note" rows="3" maxlength="800">{$site.form_res_note|default:''}</textarea>
        </label>
        <label class="field">Text der Datenschutz-Einwilligung <small>(Häkchen vor dem Absenden)</small>
            <textarea name="form_consent" rows="2" maxlength="500">{$site.form_consent|default:''}</textarea>
        </label>
        <label class="field">Hinweis unter dem Absenden-Knopf <small>(„{literal}{telefon}{/literal}“ wird durch die Telefonnummer als Anruf-Link ersetzt)</small>
            <input type="text" name="form_hint" value="{$site.form_hint|default:''}" maxlength="300">
        </label>
        <div class="grid-2">
            <label class="field">Bestätigung nach einer Anfrage
                <textarea name="form_success" rows="3" maxlength="500">{$site.form_success|default:''}</textarea>
            </label>
            <label class="field">Bestätigung nach einer Bestellung
                <textarea name="form_success_res" rows="3" maxlength="500">{$site.form_success_res|default:''}</textarea>
            </label>
        </div>
    </section>

    <section class="card" id="kontaktformular">
        <h2>Kontaktformular</h2>
        <p class="muted small">Alle Anfragen werden immer unter <a href="index.php?page=messages">Nachrichten</a> gespeichert.
            Zusätzlich kann eine Benachrichtigung per E-Mail verschickt werden.</p>
        <div class="grid-2">
            <label class="field">Benachrichtigung senden an <small>(leer = keine E-Mail)</small>
                <input type="email" name="contact_email_to" value="{$site.contact_email_to|default:''}" placeholder="z. B. info@ihre-domain.de">
            </label>
            <label class="field">Absenderadresse <small>(leer = automatisch)</small>
                <input type="email" name="contact_email_from" value="{$site.contact_email_from|default:''}" placeholder="{$mail_sender}">
            </label>
        </div>

        <h3 class="sub-heading">Versand über Ihr E-Mail-Postfach (SMTP) <small>– empfohlen</small></h3>
        <p class="muted small">Damit die Benachrichtigungen zuverlässig ankommen, verschickt die Webseite sie über ein echtes E-Mail-Postfach –
            genau wie ein E-Mail-Programm. Tragen Sie dazu die Zugangsdaten eines Postfachs ein (z.&nbsp;B. eines IONOS-Postfachs).
            Die Daten finden Sie im IONOS-Kundenbereich unter <em>E-Mail → Postfach → Einrichtung in E-Mail-Programmen</em>.</p>
        <div class="grid-2">
            <label class="field">Postausgangsserver
                <input type="text" name="smtp_host" value="{$site.smtp_host|default:''}" placeholder="smtp.ionos.de" autocomplete="off">
            </label>
            <div class="grid-2 tight">
                <label class="field">Port
                    <input type="text" name="smtp_port" value="{$site.smtp_port|default:''}" placeholder="587" inputmode="numeric" pattern="\d{literal}{2,5}{/literal}">
                </label>
                <label class="field">Verschlüsselung
                    <select name="smtp_secure">
                        {foreach $smtp_secure_options as $key => $label}
                            <option value="{$key}"{if $smtp_secure === $key} selected{/if}>{$label}</option>
                        {/foreach}
                    </select>
                </label>
            </div>
            <label class="field">Benutzername <small>(meist die vollständige E-Mail-Adresse)</small>
                <input type="text" name="smtp_user" value="{$site.smtp_user|default:''}" placeholder="webseite@ihre-domain.de" autocomplete="off">
            </label>
            <label class="field">Passwort des Postfachs
                <input type="password" name="smtp_pass" value="" autocomplete="new-password"
                       placeholder="{if $smtp_has_pass}•••••••• (gespeichert – leer lassen, um es zu behalten){else}Passwort eingeben{/if}">
                {if $smtp_has_pass}<label class="check"><input type="checkbox" name="smtp_pass_clear" value="1"> Gespeichertes Passwort löschen</label>{/if}
            </label>
        </div>

        <p class="small">Status:
            {if !$mail_recipient}<span class="pill pill--off">Keine E-Mail-Benachrichtigung</span> – bitte oben eine Empfängeradresse eintragen.
            {elseif $smtp_active}<span class="pill pill--on">Aktiv über SMTP</span> an {$mail_recipient} über {$site.smtp_host}
            {else}<span class="pill pill--plan">Aktiv ohne SMTP</span> an {$mail_recipient} – Zustellung unsicher, SMTP wird empfohlen{/if}</p>
        {if $mail_recipient}
            <p><button type="submit" form="mail-test-form" class="btn btn-small">Testmail an {$mail_recipient} senden</button>
               <span class="muted small">Erst speichern, dann testen.</span></p>
        {/if}
        <p class="muted small">„Antworten“ im E-Mail-Programm geht automatisch direkt an den Gast, der die Anfrage geschickt hat.</p>
    </section>

    <section class="card">
        <h2>Rechtliches</h2>
        <label class="field">Impressum
            <textarea name="imprint_text" rows="10">{$site.imprint_text|default:''}</textarea>
        </label>
        <label class="field">Datenschutzerklärung <small>(„## Titel“ = Zwischenüberschrift)</small>
            <textarea name="privacy_text" rows="12">{$site.privacy_text|default:''}</textarea>
        </label>
    </section>

    <div class="form-actions sticky-actions">
        <button class="btn btn-primary" type="submit">Speichern</button>
    </div>
</form>

<form method="post" action="index.php?page=mail_test" id="mail-test-form" hidden>
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
</form>
{/block}
