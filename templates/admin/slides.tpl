{extends file="admin/layout.tpl"}
{block name=title}Bannerbilder{/block}
{block name=content}
<div class="page-head">
    <h1>Bannerbilder</h1>
    <a class="btn" href="../#start" target="_blank" rel="noopener">Banner ansehen ↗</a>
</div>
<p class="muted small">Die aktiven Bilder wechseln im Banner automatisch alle paar Sekunden. Reihenfolge per Drag &amp; Drop am Griff ⠿ ändern,
    mit dem Schalter vorübergehend ausblenden. Mit „Ausschnitt“ legen Sie fest, welcher Bildteil sichtbar bleibt, wenn das Bild beschnitten wird
    (z. B. „Rechts“, wenn eine Person rechts im Bild steht).</p>

<form method="post" action="index.php?page=slides" enctype="multipart/form-data" class="card upload-card">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <label class="field">Neue Bilder hinzufügen <small>(mehrere auswählbar – Querformat, mind. 1920 × 1080 px, JPG)</small>
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required>
    </label>
    <button class="btn btn-primary" type="submit">Hochladen</button>
</form>

{if $slides}
<ul class="sortable slide-list" data-sort-type="slides">
    {foreach $slides as $s}
    <li class="card slide-row{if !$s.active} is-inactive{/if}" data-id="{$s.id}">
        <span class="handle" title="Ziehen zum Sortieren" draggable="true">⠿</span>
        <div class="slide-thumb" style="background-image: url('../{$s.image}'); background-position: {$s.position};"></div>
        <form method="post" action="index.php?page=slide_update" class="slide-form js-slide-form">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">
            <input type="hidden" name="id" value="{$s.id}">
            <label class="field">Bildbeschreibung <small>(für Suchmaschinen &amp; Screenreader)</small>
                <input type="text" name="alt" value="{$s.alt}" maxlength="200">
            </label>
            <label class="field">Ausschnitt
                <select name="position" class="js-slide-position">
                    {foreach $positions as $key => $label}
                        <option value="{$key}"{if $s.position == $key} selected{/if}>{$label}</option>
                    {/foreach}
                </select>
            </label>
            <button class="btn btn-small" type="submit">Speichern</button>
        </form>
        <div class="slide-actions">
            <label class="switch" title="Im Banner anzeigen">
                <input type="checkbox" class="js-toggle-slide" data-id="{$s.id}"{if $s.active} checked{/if}>
                <span></span>
            </label>
            <form method="post" action="index.php?page=slide_delete" class="inline" data-confirm="Dieses Bild aus dem Banner entfernen?">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <input type="hidden" name="id" value="{$s.id}">
                <button class="btn btn-small btn-danger" type="submit">Entfernen</button>
            </form>
        </div>
    </li>
    {/foreach}
</ul>
{else}
<div class="card"><p class="muted">Noch keine Bannerbilder. Ohne Bilder wird die „Bannergrafik“ aus den Einstellungen verwendet.</p></div>
{/if}
{/block}
