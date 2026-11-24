{extends file="admin/layout.tpl"}
{block name=title}Einrichtung{/block}
{block name=content}
<div class="card card--auth">
    <h1>Einrichtung</h1>
    <p class="muted">Legen Sie jetzt das Administrator-Konto für die Verwaltung an.</p>
    {include file="admin/_errors.tpl"}
    <form method="post" action="index.php?page=setup">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <label class="field">Benutzername
            <input type="text" name="username" value="{$username}" required autofocus autocomplete="username" pattern="[A-Za-z0-9_.\-]{literal}{3,50}{/literal}">
        </label>
        <label class="field">Passwort <small>(mind. 10 Zeichen)</small>
            <input type="password" name="password" required minlength="10" autocomplete="new-password">
        </label>
        <label class="field">Passwort wiederholen
            <input type="password" name="password_confirm" required minlength="10" autocomplete="new-password">
        </label>
        <button class="btn btn-primary btn-block" type="submit">Konto anlegen</button>
    </form>
</div>
{/block}
