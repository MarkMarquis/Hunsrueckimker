{if $errors}
<div class="alert alert-error">
    <ul>{foreach $errors as $e}<li>{$e}</li>{/foreach}</ul>
</div>
{/if}
