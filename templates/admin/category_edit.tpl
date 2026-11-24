{extends file="admin/layout.tpl"}
{block name=title}Kategorie{/block}
{block name=content}
<p><a href="index.php?page=menu">&larr; Zurück zur Speisekarte</a></p>
<h1>{if $id}Kategorie bearbeiten{else}Neue Kategorie{/if}</h1>
{include file="admin/_errors.tpl"}
<form method="post" action="index.php?page=category_edit{if $id}&amp;id={$id}{/if}" class="card form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <label class="field">Name
        <input type="text" name="name" value="{$cat.name}" required maxlength="100" autofocus>
    </label>
    <label class="field">Bereich auf der Webseite
        <select name="section">
            {foreach $sections as $key => $label}
                <option value="{$key}"{if $cat.section == $key} selected{/if}>{$label}</option>
            {/foreach}
        </select>
    </label>
    <label class="field">Beschreibung (optional)
        <input type="text" name="description" value="{$cat.description}" maxlength="200">
    </label>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Speichern</button>
        <a class="btn" href="index.php?page=menu">Abbrechen</a>
    </div>
</form>
{/block}
