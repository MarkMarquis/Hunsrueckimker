{extends file="admin/layout.tpl"}
{block name=title}Anmelden{/block}
{block name=content}
<div class="card card--auth">
    <h1>Anmelden</h1>
    <p class="muted">{$site.site_name|default:''} – Verwaltung</p>
    {if $error}<div class="alert alert-error">{$error}</div>{/if}
    <form method="post" action="index.php?page=login">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <label class="field">Benutzername
            <input type="text" name="username" value="{$username}" required autofocus autocomplete="username">
        </label>
        <label class="field">Passwort
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn btn-primary btn-block" type="submit">Anmelden</button>
    </form>
    <p class="muted small"><a href="../">&larr; Zurück zur Webseite</a></p>
</div>
{/block}
