{extends file="admin/layout.tpl"}
{block name=title}Speisekarte{/block}
{block name=content}
<div class="page-head">
    <h1>Speisekarte &amp; Highlights</h1>
    <div>
        <a class="btn" href="index.php?page=category_edit">+ Kategorie</a>
        <a class="btn btn-primary" href="index.php?page=item_edit">+ Gericht</a>
    </div>
</div>
<p class="muted small">Tipp: Kategorien und Gerichte lassen sich mit dem Griff ⠿ per Drag &amp; Drop sortieren. Mit dem Schalter blenden Sie ein Gericht vorübergehend aus.</p>

{if !$categories}
    <div class="card"><p class="muted">Noch keine Kategorien. Legen Sie zuerst eine Kategorie an.</p></div>
{/if}

{foreach $sections as $sectionKey => $sectionLabel}
<h2 class="section-heading">{$sectionLabel}
    <a class="btn btn-small" href="index.php?page=category_edit&amp;section={$sectionKey}">+ Kategorie</a>
</h2>
<div class="sortable" data-sort-type="categories">
{foreach $categories as $cat}{if $cat.section == $sectionKey}
    <section class="card menu-cat" data-id="{$cat.id}">
        <div class="menu-cat-head">
            <span class="handle" title="Ziehen zum Sortieren" draggable="true">⠿</span>
            <div class="menu-cat-title">
                <h2>{$cat.name}</h2>
                {if $cat.description}<p class="muted small">{$cat.description}</p>{/if}
            </div>
            <div class="actions">
                <a class="btn btn-small" href="index.php?page=item_edit&amp;category={$cat.id}">+ Gericht</a>
                <a class="btn btn-small" href="index.php?page=category_edit&amp;id={$cat.id}">Bearbeiten</a>
                <form method="post" action="index.php?page=category_delete" class="inline"
                      data-confirm="Kategorie „{$cat.name}“ mit allen {count($cat.items)} Gerichten löschen?">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$cat.id}">
                    <button class="btn btn-small btn-danger" type="submit">Löschen</button>
                </form>
            </div>
        </div>
        {if $cat.items}
        <ul class="sortable item-list" data-sort-type="items">
            {foreach $cat.items as $item}
            <li class="item-row{if !$item.active} is-inactive{/if}" data-id="{$item.id}">
                <span class="handle" title="Ziehen zum Sortieren" draggable="true">⠿</span>
                <div class="item-main">
                    <a href="index.php?page=item_edit&amp;id={$item.id}" class="item-name">{$item.name}</a>
                    {if $item.description}<span class="muted small">{$item.description}</span>{/if}
                    {if $item.tag_list}<span class="item-tags">{foreach $item.tag_list as $t}<span class="pill">{$t}</span>{/foreach}</span>{/if}{if $item.allergens}<span class="muted small">Kennzeichnung: {$item.allergens}</span>{/if}
                </div>
                <span class="item-price">{if $item.portion}<small class="muted">{$item.portion}</small> {/if}{$item.price_cents|price}{foreach $item.variant_list as $v}<br><small class="muted">{$v.label}</small> {$v.price_cents|price}{/foreach}</span>
                <label class="switch" title="Auf der Webseite anzeigen">
                    <input type="checkbox" class="js-toggle-item" data-id="{$item.id}"{if $item.active} checked{/if}>
                    <span></span>
                </label>
                <form method="post" action="index.php?page=item_delete" class="inline js-ajax-delete" data-confirm="„{$item.name}“ löschen?">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$item.id}">
                    <button class="btn btn-small btn-danger" type="submit" aria-label="Löschen">✕</button>
                </form>
            </li>
            {/foreach}
        </ul>
        {else}
            <p class="muted small">Keine Gerichte in dieser Kategorie.</p>
        {/if}
    </section>
{/if}{/foreach}
</div>
{/foreach}
{/block}
