{* Eine Sorte (Eintrag) der Honigliste. Erwartet $item *}
<li class="menu-item{if $item.image|default:''} menu-item--image{/if}">
    {if $item.image|default:''}<img class="menu-item-image" src="{$item.image|item_image}" alt="{$item.name}" loading="lazy">{/if}
    <div class="menu-item-main">
    <div class="menu-item-head">
        <span class="menu-item-name">{$item.name}{if $item.allergens} <sup class="menu-item-codes" title="Allergene / Zusatzstoffe">{$item.allergens}</sup>{/if}</span>
        <span class="menu-item-dots" aria-hidden="true"></span>
        {if $item.portion}<span class="menu-item-portion">{$item.portion}</span>{/if}
        {if $item.price_cents > 0}<span class="menu-item-price">{$item.price_cents|price}</span>{/if}
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
    </div>
</li>
