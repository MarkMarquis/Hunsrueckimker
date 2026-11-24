{extends file="admin/layout.tpl"}
{block name=title}Passwort ändern{/block}
{block name=content}
<h1>Passwort ändern</h1>
{include file="admin/_errors.tpl"}
<form method="post" action="index.php?page=password" class="card form narrow-form">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <label class="field">Aktuelles Passwort
        <input type="password" name="current_password" required autocomplete="current-password">
    </label>
    <label class="field">Neues Passwort <small>(mind. 10 Zeichen)</small>
        <input type="password" name="new_password" required minlength="10" autocomplete="new-password">
    </label>
    <label class="field">Neues Passwort wiederholen
        <input type="password" name="new_password_confirm" required minlength="10" autocomplete="new-password">
    </label>
    <div class="form-actions"><button class="btn btn-primary" type="submit">Passwort ändern</button></div>
</form>
{/block}
