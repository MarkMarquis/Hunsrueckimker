<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{block name=title}Verwaltung{/block} – {$site.site_name|default:'Backend'}</title>
    <link rel="stylesheet" href="{'../assets/css/admin.css'|asset}">
</head>
<body class="admin">
{if isset($user) && $user}
<header class="admin-header">
    <div class="admin-header-inner">
        <a class="admin-brand" href="index.php">{$site.site_name|default:'Backend'}<small>Verwaltung</small></a>
        <button class="admin-nav-toggle" type="button" aria-expanded="false" aria-controls="admin-nav">Menü</button>
        <nav class="admin-nav" id="admin-nav">
            <a href="index.php?page=dashboard"{if $page=='dashboard'} class="is-active"{/if}>Übersicht</a>
            <a href="index.php?page=settings"{if $page=='settings'} class="is-active"{/if}>Einstellungen</a>
            <a href="index.php?page=hours"{if in_array($page, ['hours','exception_edit'])} class="is-active"{/if}>Öffnungszeiten</a>
            <a href="index.php?page=slides"{if $page=='slides'} class="is-active"{/if}>Bannerbilder</a>
            <a href="index.php?page=news"{if in_array($page, ['news','news_edit'])} class="is-active"{/if}>News</a>
            <a href="index.php?page=menu"{if in_array($page, ['menu','category_edit','item_edit'])} class="is-active"{/if}>Speisekarte</a>
            <a href="index.php?page=messages"{if in_array($page, ['messages','message'])} class="is-active"{/if}>Nachrichten{if $unread_count} <span class="badge">{$unread_count}</span>{/if}</a>
            <a href="index.php?page=password"{if $page=='password'} class="is-active"{/if}>Passwort</a>
            <a href="../" target="_blank" rel="noopener">Webseite ansehen ↗</a>
            <form method="post" action="index.php?page=logout" class="inline">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <button type="submit" class="link-button">Abmelden ({$user.username})</button>
            </form>
        </nav>
    </div>
</header>
{/if}

<main class="admin-main{if !isset($user) || !$user} admin-main--center{/if}">
    {foreach $flash as $f}
        <div class="alert alert-{$f.type}">{$f.message}</div>
    {/foreach}
    {block name=content}{/block}
</main>

<script src="{'../assets/vendor/jquery-3.7.1.min.js'|asset}"></script>
<script>window.CSRF_TOKEN = "{$csrf_token}";</script>
<script src="{'../assets/js/admin.js'|asset}"></script>
</body>
</html>
