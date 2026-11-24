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
        <h2><span class="pill pill--res">Reservierungsanfrage</span></h2>
        <dl class="meta">
            <dt>Datum</dt><dd><strong>{$msg.res_date|weekday_de}, {$msg.res_date|date_de}</strong></dd>
            <dt>Uhrzeit</dt><dd><strong>{$msg.res_time} Uhr</strong></dd>
            <dt>Personen</dt><dd><strong>{$msg.persons}</strong></dd>
            <dt>Bereich</dt><dd>{$msg.area|area_label|default:'Keine Präferenz'}</dd>
        </dl>
        <p class="muted small">Bitte Verfügbarkeit prüfen und dem Gast per E-Mail{if $msg.phone} oder Telefon{/if} bestätigen oder absagen.</p>
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
