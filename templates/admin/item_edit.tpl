{extends file="admin/layout.tpl"}
{block name=title}Gericht{/block}
{block name=content}
<p><a href="index.php?page=menu">&larr; Zurück zur Speisekarte</a></p>
<h1>{if $id}Gericht bearbeiten{else}Neues Gericht{/if}</h1>
{include file="admin/_errors.tpl"}
<form method="post" action="index.php?page=item_edit{if $id}&amp;id={$id}{/if}" class="card form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <div class="grid-2">
        <label class="field">Name
            <input type="text" name="name" value="{$item.name}" required maxlength="150" autofocus>
        </label>
        <label class="field">Kategorie
            <select name="category_id" required>
                {foreach $categories as $c}
                    <option value="{$c.id}"{if $c.id == $item.category_id} selected{/if}>{$c.name}</option>
                {/foreach}
            </select>
        </label>
    </div>
    <label class="field">Beschreibung
        <textarea name="description" rows="3" maxlength="500">{$item.description}</textarea>
    </label>
    <fieldset class="field price-set">
        <legend>Preis</legend>
        <div class="variant-row">
            <label>Portion / Gewicht <small>(optional)</small>
                <input type="text" name="portion" value="{$item.portion}" maxlength="80" placeholder="z. B. Rohgewicht ca. 350 g">
            </label>
            <label>Preis in €
                <input type="text" name="price" value="{($item.price_cents/100)|string_format:'%.2f'|replace:'.':','}" inputmode="decimal" pattern="\d+([.,]\d{literal}{1,2}{/literal})?" required>
            </label>
        </div>
        <div id="variants">
            {foreach $item.variant_list as $v}
            <div class="variant-row">
                <label>Weitere Portion
                    <input type="text" name="variant_label[]" value="{$v.label}" maxlength="80">
                </label>
                <label>Preis in €
                    <input type="text" name="variant_price[]" value="{($v.price_cents/100)|string_format:'%.2f'|replace:'.':','}" inputmode="decimal">
                </label>
                <button type="button" class="btn btn-small btn-danger js-remove-variant" aria-label="Variante entfernen">✕</button>
            </div>
            {/foreach}
        </div>
        <template id="variant-template">
            <div class="variant-row">
                <label>Weitere Portion
                    <input type="text" name="variant_label[]" maxlength="80" placeholder="z. B. Rohgewicht ca. 500 g">
                </label>
                <label>Preis in €
                    <input type="text" name="variant_price[]" inputmode="decimal">
                </label>
                <button type="button" class="btn btn-small btn-danger js-remove-variant" aria-label="Variante entfernen">✕</button>
            </div>
        </template>
        <button type="button" class="btn btn-small" id="add-variant">+ Weitere Portionsgröße</button>
    </fieldset>
    <div class="grid-2">
        <label class="field">Allergene / Zusatzstoffe <small>(Kennzeichnung, z. B. „12, G“)</small>
            <input type="text" name="allergens" value="{$item.allergens}" maxlength="100">
        </label>
        <label class="field">Hinweise <small>(mit Komma getrennt, z. B. „vegetarisch, scharf“)</small>
            <input type="text" name="tags" value="{$item.tags}" maxlength="200">
        </label>
    </div>
    <label class="check"><input type="checkbox" name="active" value="1"{if $item.active} checked{/if}> Auf der Webseite anzeigen</label>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Speichern</button>
        <a class="btn" href="index.php?page=menu">Abbrechen</a>
    </div>
</form>
{/block}
