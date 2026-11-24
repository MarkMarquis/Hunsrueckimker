{extends file="admin/layout.tpl"}
{block name=title}News{/block}
{block name=content}
<div class="page-head">
    <h1>News</h1>
    <a class="btn btn-primary" href="index.php?page=news_edit">+ Neue Neuigkeit</a>
</div>
{if $items}
<div class="card card--flush">
<table class="table">
    <thead><tr><th>Datum</th><th>Titel</th><th>Status</th><th class="actions">Aktionen</th></tr></thead>
    <tbody>
    {foreach $items as $n}
        <tr>
            <td data-label="Datum">{$n.published_at|date_de}</td>
            <td data-label="Titel"><a href="index.php?page=news_edit&amp;id={$n.id}">{$n.title}</a></td>
            <td data-label="Status">
                {if !$n.published}<span class="pill pill--off">Entwurf</span>
                {elseif $n.published_at > $smarty.now|date_format:'%Y-%m-%d'}<span class="pill pill--plan">Geplant</span>
                {else}<span class="pill pill--on">Online</span>{/if}
            </td>
            <td class="actions">
                <a class="btn btn-small" href="index.php?page=news_edit&amp;id={$n.id}">Bearbeiten</a>
                <form method="post" action="index.php?page=news_delete" class="inline" data-confirm="„{$n.title}“ wirklich löschen?">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="id" value="{$n.id}">
                    <button class="btn btn-small btn-danger" type="submit">Löschen</button>
                </form>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
</div>
{else}
<div class="card"><p class="muted">Noch keine Neuigkeiten vorhanden.</p></div>
{/if}
{/block}
