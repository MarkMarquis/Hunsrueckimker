{* Ein Gericht der Speisekarte. Erwartet $item *}
<li class="menu-item">
    <div class="menu-item-head">
        <span class="menu-item-name">{$item.name}{if $item.allergens} <sup class="menu-item-codes" title="Allergene / Zusatzstoffe">{$item.allergens}</sup>{/if}</span>
        <span class="menu-item-dots" aria-hidden="true"></span>
        {if $item.portion}<span class="menu-item-portion">{$item.portion}</span>{/if}
        <span class="menu-item-price">{$item.price_cents|price}</span>
    </div>
    {foreach $item.variant_list as $v}
        <div class="menu-item-variant">
            <span class="menu-item-portion">{$v.label}</span>
            <span class="menu-item-price">{$v.price_cents|price}</span>
        </div>
    {/foreach}
    {if $item.description}<p class="menu-item-desc">{$item.description}</p>{/if}
    {if $item.tag_list}
        <p class="menu-item-tags">{foreach $item.tag_list as $tag}<span class="tag">{$tag}</span>{/foreach}</p>
    {/if}
</li>
