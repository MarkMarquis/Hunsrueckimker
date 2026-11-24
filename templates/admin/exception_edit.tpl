{extends file="admin/layout.tpl"}
{block name=title}Ausnahme{/block}
{block name=content}
<p><a href="index.php?page=hours">&larr; Zurück zu den Öffnungszeiten</a></p>
<h1>{if $id}Ausnahme bearbeiten{else}Ausnahme erfassen{/if}</h1>
{include file="admin/_errors.tpl"}
<form method="post" action="index.php?page=exception_edit{if $id}&amp;id={$id}{/if}" class="card form js-exception-form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <label class="field">Bezeichnung <small>(z. B. „Heiligabend“, „Ostermontag“, „Betriebsferien“)</small>
        <input type="text" name="title" value="{$exc.title}" maxlength="100">
    </label>
    <div class="grid-2">
        <label class="field">Datum (von) *
            <input type="date" name="date_from" value="{$exc.date_from}" required>
        </label>
        <label class="field">Datum (bis) <small>(nur bei mehreren Tagen)</small>
            <input type="date" name="date_to" value="{$exc.date_to}">
        </label>
    </div>
    <div class="field">
        <span class="field-label">Art der Ausnahme</span>
        {foreach $types as $key => $label}
            <label class="check"><input type="radio" name="type" value="{$key}" class="js-exc-type"{if $exc.type == $key} checked{/if}> {$label}</label>
        {/foreach}
    </div>
    <div class="grid-2 js-exc-times">
        <label class="field">Geöffnet ab
            <input type="time" name="open_time" value="{$exc.open_time}" step="900">
        </label>
        <label class="field">bis <small>(optional)</small>
            <input type="time" name="close_time" value="{$exc.close_time}" step="900">
        </label>
    </div>
    <label class="field">Zusätzlicher Hinweistext <small>(optional, erscheint in der Meldung)</small>
        <textarea name="note" rows="3" maxlength="1000" placeholder="z. B. Wir wünschen Ihnen frohe Feiertage!">{$exc.note}</textarea>
    </label>
    <label class="field">Hinweis in „Aktuelles“ anzeigen ab … Tage vorher <small>(0 = keinen Hinweis anzeigen)</small>
        <input type="number" name="announce_days" value="{$exc.announce_days}" min="0" max="120" style="max-width:140px">
    </label>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Speichern</button>
        <a class="btn" href="index.php?page=hours">Abbrechen</a>
    </div>
</form>
{/block}
