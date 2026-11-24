<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{$title} – {$site.site_name}</title>
    {if $logo_url}<link rel="icon" href="{$logo_url}">{/if}
    <link rel="stylesheet" href="{'assets/css/style.css'|asset}">
</head>
<body class="legal-page">
<header class="site-header is-solid">
    <div class="container header-inner">
        <a class="brand" href="./">{$site.site_name}{if $site.site_subtitle}<small>{$site.site_subtitle}</small>{/if}</a>
        <a class="back-link" href="./">&larr; Zur Startseite</a>
    </div>
</header>
<main class="section">
    <div class="container narrow">
        <h1 class="section-title">{$title}</h1>
        <div class="ornament" aria-hidden="true"></div>
        <div class="prose">{$text|paragraphs nofilter}</div>
    </div>
</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <p>&copy; {$year} {$site.site_name} · <a href="impressum.php">Impressum</a> · <a href="datenschutz.php">Datenschutz</a></p>
    </div>
</footer>
</body>
</html>
