{extends file="admin/layout.tpl"}
{block name=title}Öffnungszeiten{/block}
{block name=content}
<h1>Öffnungszeiten</h1>

<form method="post" action="index.php?page=hours" class="card form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <h2>Feste Wochenzeiten</h2>
    <p class="muted small">„Bis“ leer lassen, wenn kein festes Ende angegeben werden soll (Anzeige: „ab 11:00 Uhr“).</p>
    <table class="table hours-admin">
        <thead><tr><th>Tag</th><th>Ruhetag</th><th>Von</th><th>Bis</th></tr></thead>
        <tbody>
        {foreach $days as $d => $name}
            {assign var=w value=$week[$d]}
            <tr class="js-day{if $w.closed} is-closed{/if}">
                <td data-label="Tag"><strong>{$name}</strong></td>
                <td data-label="Ruhetag"><label class="check"><input type="checkbox" class="js-closed" name="week[{$d}][closed]" value="1"{if $w.closed} checked{/if}> geschlossen</label></td>
                <td data-label="Von"><input type="time" name="week[{$d}][open]" value="{$w.open}" step="900"></td>
                <td data-label="Bis"><input type="time" name="week[{$d}][close]" value="{$w.close}" step="900"></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    <label class="field" style="margin-top:16px">Zusätzlicher Hinweis <small>(z. B. „Küche durchgehend geöffnet“)</small>
        <input type="text" name="opening_note" value="{$site.opening_note|default:''}" maxlength="200">
    </label>
    <div class="form-actions"><button class="btn btn-primary" type="submit">Wochenzeiten speichern</button></div>
</form>

<div class="page-head">
    <h2>Ausnahmen (Feiertage, Betriebsferien …)</h2>
    <a class="btn btn-primary" href="index.php?page=exception_edit">+ Ausnahme erfassen</a>
</div>
<p class="muted small">Jede Ausnahme erscheint automatisch einige Wochen vorher als hervorgehobener Hinweis im Bereich „Aktuelles“ (und im Banner)
    und bei den Öffnungszeiten auf der Webseite. Nach dem Termin verschwindet sie von selbst.</p>
{if $exceptions}
<div class="card card--flush">
<table class="table">
    <thead><tr><th>Wann</th><th>Art</th><th>Hinweis in „Aktuelles“</th><th class="actions">Aktionen</th></tr></thead>
    <tbody>
    {foreach $exceptions as $e}
        <tr class="{if $e.end < $today}is-past{/if}">
            <td data-label="Wann"><strong>{$e.when}</strong>{if $e.title}<br><span class="muted small">{$e.title}</span>{/if}</td>
            <td data-label="Art">{$types[$e.type]}{if $e.type != 'closed'}<br><span class="muted small">{$e.short}</span>{/if}</td>
            <td data-label="Hinweis">
                {if $e.end < $today}<span class="pill pill--off">vorbei</span>
                {elseif $e.announce_days == 0}<span class="pill pill--off">kein Hinweis</span>
                {elseif $e.announce_from <= $today}<span class="pill pill--on">wird angezeigt</span>
                {else}<span class="pill pill--plan">ab {$e.announce_from|date_de}</span>{/if}
            </td>
            <td class="actions">
                <a class="btn btn-small" href="index.php?page=exception_edit&amp;id={$e.id}">Bearbeiten</a>
                <form method="post" action="index.php?page=exception_delete" class="inline" data-confirm="Ausnahme „{$e.when}“ löschen?">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$e.id}">
                    <button class="btn btn-small btn-danger" type="submit">Löschen</button>
                </form>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
</div>
{else}
<div class="card"><p class="muted">Noch keine Ausnahmen erfasst.</p></div>
{/if}
{/block}
