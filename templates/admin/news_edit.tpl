{extends file="admin/layout.tpl"}
{block name=title}{if $id}News bearbeiten{else}Neue Neuigkeit{/if}{/block}
{block name=content}
<p><a href="index.php?page=news">&larr; Zurück zur Übersicht</a></p>
<h1>{if $id}Neuigkeit bearbeiten{else}Neue Neuigkeit{/if}</h1>
{include file="admin/_errors.tpl"}
<form method="post" action="index.php?page=news_edit{if $id}&amp;id={$id}{/if}" enctype="multipart/form-data" class="card form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <label class="field">Titel
        <input type="text" name="title" value="{$item.title}" required maxlength="200">
    </label>
    <label class="field">Text <small>(Leerzeile = neuer Absatz)</small>
        <textarea name="content" rows="10">{$item.content}</textarea>
    </label>
    <div class="grid-2">
        <label class="field">Veröffentlichen ab
            <input type="date" name="published_at" value="{$item.published_at}" required>
        </label>
        <div class="field">
            <span class="field-label">Status</span>
            <label class="check"><input type="checkbox" name="published" value="1"{if $item.published} checked{/if}> Auf der Webseite anzeigen</label>
        </div>
    </div>
    <div class="field">
        <span class="field-label">Bild (optional)</span>
        <div class="image-preview" data-preview="image">
            {if $item.image}<img src="../{$upload_url}/{$item.image}" alt="">{else}<span class="muted small">Kein Bild</span>{/if}
        </div>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" data-preview-target="image">
        {if $item.image}<label class="check"><input type="checkbox" name="remove_image" value="1"> Bild entfernen</label>{/if}
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Speichern</button>
        <a class="btn" href="index.php?page=news">Abbrechen</a>
    </div>
</form>
{/block}
