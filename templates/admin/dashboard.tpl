{extends file="admin/layout.tpl"}
{block name=title}Übersicht{/block}
{block name=content}
<h1>Willkommen, {$user.username}</h1>
<div class="stats">
    <a class="stat" href="index.php?page=news"><strong>{$news_count}</strong><span>News-Einträge</span></a>
    <a class="stat" href="index.php?page=menu"><strong>{$cat_count}</strong><span>Kategorien</span></a>
    <a class="stat" href="index.php?page=menu"><strong>{$item_count}</strong><span>Sorten</span></a>
    <a class="stat{if $unread_count} stat--hot{/if}" href="index.php?page=messages"><strong>{$unread_count}</strong><span>Ungelesene Nachrichten</span></a>
</div>

<div class="grid-2">
    <section class="card">
        <h2>Schnellzugriff</h2>
        <ul class="quick">
            <li><a href="index.php?page=news_edit">+ Neue Neuigkeit schreiben</a></li>
            <li><a href="index.php?page=item_edit">+ Neue Sorte anlegen</a></li>
            <li><a href="index.php?page=settings">Öffnungszeiten &amp; Kontaktdaten ändern</a></li>
            <li><a href="index.php?page=settings#bilder">Logo &amp; Banner austauschen</a></li>
        </ul>
    </section>
    <section class="card">
        <h2>Neueste Nachrichten</h2>
        {if $latest_msgs}
            <ul class="msg-mini">
            {foreach $latest_msgs as $m}
                <li class="{if !$m.is_read}is-unread{/if}">
                    <a href="index.php?page=message&amp;id={$m.id}">{$m.name}{if $m.subject} – {$m.subject}{/if}</a>
                    <span class="muted small">{$m.created_at|date_de:'d.m.Y H:i'}</span>
                </li>
            {/foreach}
            </ul>
        {else}
            <p class="muted">Noch keine Nachrichten.</p>
        {/if}
    </section>
</div>
{/block}
