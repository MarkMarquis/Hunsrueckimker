{extends file="admin/layout.tpl"}
{block name=title}Nachricht{/block}
{block name=content}
<p><a href="index.php?page=messages">&larr; Alle Nachrichten</a></p>
<div class="card">
    <h1>{$msg.subject|default:'(ohne Betreff)'}</h1>
    <dl class="meta">
        <dt>Von</dt><dd>{$msg.name}</dd>
        <dt>E-Mail</dt><dd><a href="mailto:{$msg.email}?subject={('Re: '|cat:($msg.subject|default:'Ihre Anfrage'))|urlencode}">{$msg.email}</a></dd>
        {if $msg.phone}<dt>Telefon</dt><dd><a href="tel:{$msg.phone}">{$msg.phone}</a></dd>{/if}
        <dt>Eingang</dt><dd>{$msg.created_at|date_de:'d.m.Y H:i'} Uhr</dd>
    </dl>
    {if $msg.type == 'reservierung'}
    <div class="reservation-box">
        <h2><span class="pill pill--res">Honigbestellung</span></h2>
        <dl class="meta">
            <dt>Lieferart</dt><dd><strong>{$msg.area|area_label|default:'Abholung'}</strong></dd>
            <dt>Gläser</dt><dd><strong>{if $msg.persons}{$msg.persons}{else}–{/if}</strong></dd>
            <dt>Wunschtermin</dt><dd>{if $msg.res_date}{$msg.res_date|weekday_de}, {$msg.res_date|date_de}{else}–{/if}</dd>
        </dl>
        <p class="muted small">Die Bestellung steht unten im Nachrichtentext. Bitte Verfügbarkeit prüfen und dem Kunden per E-Mail{if $msg.phone} oder Telefon{/if} antworten.</p>
    </div>
    {/if}
    {if $msg.mail_error|default:''}
        <div class="alert alert-error">Die E-Mail-Benachrichtigung zu dieser Anfrage konnte nicht verschickt werden:<br>{$msg.mail_error}</div>
    {/if}
    <div class="message-body">{$msg.message|paragraphs nofilter}</div>
    <div class="form-actions">
        <a class="btn btn-primary" href="mailto:{$msg.email}?subject={('Re: '|cat:($msg.subject|default:'Ihre Anfrage'))|urlencode}">Per E-Mail antworten</a>
        <form method="post" action="index.php?page=message_unread" class="inline">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            <input type="hidden" name="id" value="{$msg.id}">
            <button class="btn" type="submit">Als ungelesen markieren</button>
        </form>
        <form method="post" action="index.php?page=message_delete" class="inline" data-confirm="Nachricht löschen?">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            <input type="hidden" name="id" value="{$msg.id}">
            <button class="btn btn-danger" type="submit">Löschen</button>
        </form>
    </div>
</div>
{/block}
