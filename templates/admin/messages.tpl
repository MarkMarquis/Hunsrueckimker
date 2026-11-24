{extends file="admin/layout.tpl"}
{block name=title}Nachrichten{/block}
{block name=content}
<h1>Anfragen &amp; Bestellungen</h1>
{if $messages}
<div class="card card--flush">
<table class="table">
    <thead><tr><th>Eingang</th><th>Von</th><th>Betreff</th><th class="actions">Aktionen</th></tr></thead>
    <tbody>
    {foreach $messages as $m}
        <tr class="{if !$m.is_read}is-unread{/if}">
            <td data-label="Eingang">{$m.created_at|date_de:'d.m.Y H:i'}</td>
            <td data-label="Von">{$m.name}<br><span class="muted small">{$m.email}</span></td>
            <td data-label="Betreff">
                {if $m.type == 'reservierung'}<span class="pill pill--res">Bestellung</span>
                    <strong>{$m.area|area_label}{if $m.persons} · {$m.persons} Gläser{/if}{if $m.res_date} · {$m.res_date|date_de}{/if}</strong><br>{/if}
                <a href="index.php?page=message&amp;id={$m.id}">{$m.subject|default:'(ohne Betreff)'}</a>
                {if $m.mail_error|default:''}<br><span class="pill pill--off" title="{$m.mail_error}">E-Mail nicht zugestellt</span>{/if}</td>
            <td class="actions">
                <a class="btn btn-small" href="index.php?page=message&amp;id={$m.id}">Lesen</a>
                <form method="post" action="index.php?page=message_delete" class="inline" data-confirm="Nachricht von {$m.name} löschen?">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$m.id}">
                    <button class="btn btn-small btn-danger" type="submit">Löschen</button>
                </form>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
</div>
{else}
<div class="card"><p class="muted">Noch keine Nachrichten eingegangen.</p></div>
{/if}
{/block}
