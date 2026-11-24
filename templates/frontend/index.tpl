<!DOCTYPE html>
<html lang="de" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$site.site_name}{if $site.site_subtitle} – {$site.site_subtitle}{/if}</title>
    <meta name="description" content="{$site.meta_description}">
    <meta property="og:title" content="{$site.site_name}">
    <meta property="og:description" content="{$site.meta_description}">
    {if $logo_url}<link rel="icon" href="{$logo_url}">{/if}
    <link rel="stylesheet" href="{'assets/css/style.css'|asset}">
</head>
<body class="{if $slides}has-hero-image{/if}{if $logo_url} has-badge{/if}">

<header class="site-header" id="top">
    <div class="container header-inner">
        <a class="brand" href="#start">{$site.site_name}{if $site.site_subtitle}<small>{$site.site_subtitle}</small>{/if}</a>
        <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false">
            <span class="sr-only">Menü öffnen</span><span></span><span></span><span></span>
        </button>
        <nav id="main-nav" class="main-nav" aria-label="Hauptnavigation">
            <ul>
                <li><a href="#start">Start</a></li>
                <li><a href="#aktuelles">Aktuelles</a></li>
                <li><a href="#highlight">Highlights</a></li>
                <li><a href="#speisekarte">Speisekarte</a></li>
                <li><a href="#kontakt">Kontakt</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    {* ---------- Banner: Bildwechsler, rundes Logo, aktuelle Meldung ---------- *}
    <section id="start" class="hero{if $slides} hero--image{/if}"{if $slides|count > 1} aria-roledescription="carousel" aria-label="Bilder"{/if}>
        {if $slides}
        <div class="hero-slides">
            {foreach $slides as $s}
            <div class="hero-slide{if $s@first} is-active{/if}" role="img" aria-label="{$s.alt}"{if !$s@first} aria-hidden="true"{/if}
                 style="background-image: url('{$s.url}'); background-position: {$s.position};"></div>
            {/foreach}
        </div>
        {/if}

        {if $logo_url}
        <a class="hero-badge" href="#start" aria-label="{$site.site_name} – zum Seitenanfang">
            <img src="{$logo_url}" alt="Logo {$site.site_name}">
        </a>
        {/if}

        <div class="container hero-inner{if $hero_news} hero-inner--split{/if}">
            <div class="hero-brand">
                <h1 class="hero-title">{$site.site_name}</h1>
                {if $site.site_subtitle}<p class="hero-subtitle">{$site.site_subtitle}</p>{/if}
                {if $site.slogan}<p class="hero-slogan">{$site.slogan}</p>{/if}
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#speisekarte">Zur Speisekarte</a>
                    <a class="btn btn-outline js-reserve" href="#reservierung">Tisch reservieren</a>
                </div>
            </div>

            {if $hero_news}
            <a class="hero-news" href="#news-{$hero_news.id}" data-news-id="{$hero_news.id}">
                {if $hero_news.image}
                    <img class="hero-news-image" src="{$upload_url}/{$hero_news.image}" alt="">
                {/if}
                <span class="hero-news-body">
                    <span class="hero-news-label">{if $hero_news.notice|default:false}Öffnungszeiten · {$hero_news.when}{else}Aktuell · <time datetime="{$hero_news.published_at}">{$hero_news.published_at|date_de}</time>{/if}</span>
                    <strong class="hero-news-title">{$hero_news.title}</strong>
                    {if $hero_news.content}<span class="hero-news-text">{$hero_news.content|excerpt:150}</span>{/if}
                    <span class="hero-news-more">Weiterlesen &rarr;</span>
                </span>
            </a>
            {/if}
        </div>

        {if $slides|count > 1}
        <button type="button" class="hero-arrow hero-arrow--prev" aria-label="Vorheriges Bild">&#8249;</button>
        <button type="button" class="hero-arrow hero-arrow--next" aria-label="Nächstes Bild">&#8250;</button>
        <div class="hero-dots" role="group" aria-label="Bild auswählen">
            {foreach $slides as $s}
            <button type="button" class="hero-dot{if $s@first} is-active{/if}" aria-label="Bild {$s@iteration} von {$slides|count}"{if $s@first} aria-current="true"{/if}></button>
            {/foreach}
        </div>
        {/if}
    </section>

    {* ---------- Aktuelles: Willkommenstext und Neuigkeiten ---------- *}
    <section id="aktuelles" class="section">
        <div class="container{if $about_url} welcome{else} narrow{/if}">
            {if $about_url}
                <figure class="welcome-image">
                    <img src="{$about_url}" alt="{$site.about_title|default:''}" loading="lazy">
                </figure>
            {/if}
            <div class="welcome-text">
                <h2 class="section-title">{$site.about_title|default:'Herzlich willkommen'}</h2>
                <div class="ornament" aria-hidden="true"></div>
                <div class="prose">{$site.about_text|paragraphs nofilter}</div>
            </div>
        </div>
        {if $news}
        <div class="container news-wrap" id="news">
            <h3 class="news-heading"><span>Neuigkeiten</span></h3>
            <div class="news-grid">
                {foreach $news as $n}
                <article class="news-card{if $n.hidden} is-hidden{/if}{if $n.notice|default:false} news-card--notice{/if}" id="news-{$n.id}">
                    {if $n.image}
                        <img class="news-image" src="{$upload_url}/{$n.image}" alt="" loading="lazy">
                    {/if}
                    <div class="news-body">
                        {if $n.notice|default:false}
                            <span class="news-badge">Öffnungszeiten · {$n.when}</span>
                        {else}
                            <time datetime="{$n.published_at}">{$n.published_at|date_de}</time>
                        {/if}
                        <h3>{$n.title}</h3>
                        <div class="news-text">{$n.content|paragraphs nofilter}</div>
                        <button type="button" class="news-more" hidden>Weiterlesen</button>
                    </div>
                </article>
                {/foreach}
            </div>
            {if $regular > 3}
                <div class="center"><button type="button" class="btn btn-outline" id="news-load-more">Ältere Meldungen anzeigen</button></div>
            {/if}
        </div>
        {/if}
    </section>

    {* ---------- Highlight-Bereich ---------- *}
    <section id="highlight" class="section section--dark">
        <div class="container">
            <h2 class="section-title">{$site.highlight_title|default:'Highlights'}</h2>
            <div class="ornament ornament--light" aria-hidden="true"></div>
            <div class="highlight-grid{if !$highlight_url} highlight-grid--noimg{/if}">
                {if $highlight_url}
                    <figure class="highlight-image"><img src="{$highlight_url}" alt="{$site.highlight_title|default:''}" loading="lazy"></figure>
                {/if}
                <div class="highlight-story prose prose--left">{$site.highlight_text|paragraphs nofilter}</div>
            </div>

            {if $highlight_menu}
            <div class="highlight-menu" id="highlight-karte">
                {foreach $highlight_menu as $cat}
                <div class="highlight-card">
                    <h3 class="menu-category-title">{$cat.name}</h3>
                    {if $cat.description}<p class="menu-category-desc">{$cat.description}</p>{/if}
                    <ul class="menu-list">
                        {foreach $cat.items as $item}
                            {include file="partials/menu_item.tpl" item=$item}
                        {/foreach}
                    </ul>
                </div>
                {/foreach}
            </div>
            {/if}
        </div>
    </section>

    {* ---------- Speisekarte ---------- *}
    <section id="speisekarte" class="section">
        <div class="container narrow">
            <h2 class="section-title">Speisekarte</h2>
            <div class="ornament" aria-hidden="true"></div>
            {if $menu}
                <div class="menu-tabs" role="tablist">
                    {if $highlight_menu}
                        <a class="menu-tab-link" href="#highlight-karte" title="Zu den Highlights weiter oben">Highlights <span aria-hidden="true">↑</span></a>
                    {/if}
                    {foreach $menu as $cat}
                        <button type="button" role="tab" class="menu-tab{if $cat@first} is-active{/if}"
                                aria-selected="{if $cat@first}true{else}false{/if}"
                                aria-controls="menu-cat-{$cat.id}" id="menu-tab-{$cat.id}">{$cat.name}</button>
                    {/foreach}
                </div>
                {foreach $menu as $cat}
                <div class="menu-category{if $cat@first} is-active{/if}" id="menu-cat-{$cat.id}"
                     role="tabpanel" aria-labelledby="menu-tab-{$cat.id}">
                    <h3 class="menu-category-title">{$cat.name}</h3>
                    {if $cat.description}<p class="menu-category-desc">{$cat.description}</p>{/if}
                    <ul class="menu-list">
                        {foreach $cat.items as $item}
                        {include file="partials/menu_item.tpl" item=$item}
                        {/foreach}
                    </ul>
                </div>
                {/foreach}
                {if $site.menu_note}<div class="menu-note">{$site.menu_note|paragraphs nofilter}</div>{/if}
                {if $site.allergen_legend}
                    <details class="allergens">
                        <summary>Allergene &amp; Zusatzstoffe</summary>
                        <div class="allergens-body">{$site.allergen_legend|paragraphs nofilter}</div>
                    </details>
                {/if}
            {else}
                <p class="center">Unsere Speisekarte wird gerade überarbeitet.</p>
            {/if}
        </div>
    </section>

    {* ---------- Kontakt ---------- *}
    <section id="kontakt" class="section section--alt">
        <div class="container">
            <h2 class="section-title">{$site.contact_title|default:'Kontakt & Reservierung'}</h2>
            <div class="ornament" aria-hidden="true"></div>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Öffnungszeiten</h3>
                    <p class="hours-today{if $hours_today.closed} is-closed{/if}"><span class="dot" aria-hidden="true"></span>{$hours_today.text}</p>
                    <table class="hours-table">
                        {foreach $hours_rows as $r}
                        <tr class="{if $r.is_today}is-today{/if}{if $r.closed} is-closed{/if}">
                            <th scope="row">{$r.label}</th><td>{$r.text}</td>
                        </tr>
                        {/foreach}
                    </table>
                    {if $site.opening_note|default:''}<p class="hours-note">{$site.opening_note}</p>{/if}
                    {if $hours_upcoming}
                        <div class="hours-exceptions">
                            <h4>Abweichende Öffnungszeiten</h4>
                            <ul>
                                {foreach $hours_upcoming as $e}
                                <li><strong>{$e.when}{if $e.title}<span class="muted"> · {$e.title}</span>{/if}</strong><br>{$e.short}</li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    <h3>So finden Sie uns</h3>
                    <address>
                        <strong>{$site.site_name}</strong><br>
                        {if $site.site_subtitle}{$site.site_subtitle}<br>{/if}
                        {foreach $address_lines as $line}{$line}<br>{/foreach}
                    </address>
                    {if $site.phone}<p>Telefon: <a href="tel:{$site.phone|regex_replace:'/[^0-9+]/':''}">{$site.phone}</a></p>{/if}
                    {if $site.fax|default:''}<p>Telefax: {$site.fax}</p>{/if}
                    {if $site.email}<p>E-Mail: <a href="mailto:{$site.email}">{$site.email}</a></p>{/if}
                    {if $address_lines}
                        <p><a class="btn btn-outline btn-small" target="_blank" rel="noopener"
                              href="https://www.openstreetmap.org/search?query={$map_query|urlencode}">Route planen</a></p>
                    {/if}
                </div>

                <div class="contact-form-wrap">
                    {assign var=isRes value=(($contact.old.type|default:'') == 'reservierung')}
                    <h3>{$site.form_title|default:'Anfrage & Tischreservierung'}</h3>
                    {if $site.form_intro|default:''}<p class="form-intro">{$site.form_intro}</p>{/if}
                    <div class="form-alert{if $contact} is-visible {if $contact.ok}is-success{else}is-error{/if}{/if}" role="status" aria-live="polite">{if $contact}{$contact.message}{/if}</div>
                    <form class="contact-form{if $isRes} is-reservation{/if}" id="contact-form" method="post" action="contact.php" novalidate>
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                        <div class="hp" aria-hidden="true">
                            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <fieldset class="type-switch" id="reservierung">
                            <legend>Ihr Anliegen</legend>
                            <label class="type-option">
                                <input type="radio" name="type" value="anfrage"{if !$isRes} checked{/if}>
                                <span>Allgemeine Anfrage</span>
                            </label>
                            <label class="type-option">
                                <input type="radio" name="type" value="reservierung"{if $isRes} checked{/if}>
                                <span>Tisch reservieren</span>
                            </label>
                        </fieldset>

                        <div class="res-fields">
                            <div class="form-row">
                                <div class="field">
                                    <label for="cf-date">Datum *</label>
                                    <input id="cf-date" name="res_date" type="date" min="{$today}" max="{$max_date}" value="{$contact.old.res_date|default:''}">
                                    <span class="field-error">{$contact.errors.res_date|default:''}</span>
                                </div>
                                <div class="field">
                                    <label for="cf-time">Uhrzeit *</label>
                                    <input id="cf-time" name="res_time" type="time" step="900" value="{$contact.old.res_time|default:''}">
                                    <span class="field-error">{$contact.errors.res_time|default:''}</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="field">
                                    <label for="cf-persons">Anzahl Personen *</label>
                                    <input id="cf-persons" name="persons" type="number" min="1" max="{$max_persons}" inputmode="numeric" value="{$contact.old.persons|default:''}">
                                    <span class="field-error">{$contact.errors.persons|default:''}</span>
                                </div>
                                <div class="field">
                                    <label for="cf-area">Gewünschter Bereich</label>
                                    <select id="cf-area" name="area">
                                        {foreach $areas as $key => $area}
                                            <option value="{$key}" data-min="{$area[1]}"{if ($contact.old.area|default:'egal') == $key} selected{/if}>{$area[0]}</option>
                                        {/foreach}
                                    </select>
                                    <span class="field-error">{$contact.errors.area|default:''}</span>
                                </div>
                            </div>
                            {if $site.form_res_note|default:''}<p class="res-note">{$site.form_res_note}</p>{/if}
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label for="cf-name">Name *</label>
                                <input id="cf-name" name="name" type="text" required maxlength="100" autocomplete="name" value="{$contact.old.name|default:''}">
                                <span class="field-error">{$contact.errors.name|default:''}</span>
                            </div>
                            <div class="field">
                                <label for="cf-email">E-Mail *</label>
                                <input id="cf-email" name="email" type="email" required maxlength="200" autocomplete="email" value="{$contact.old.email|default:''}">
                                <span class="field-error">{$contact.errors.email|default:''}</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="cf-phone">Telefon <span class="res-only">(für Rückfragen empfohlen)</span></label>
                                <input id="cf-phone" name="phone" type="tel" maxlength="50" autocomplete="tel" value="{$contact.old.phone|default:''}">
                                <span class="field-error">{$contact.errors.phone|default:''}</span>
                            </div>
                            <div class="field">
                                <label for="cf-subject">Betreff</label>
                                <input id="cf-subject" name="subject" type="text" maxlength="150" value="{$contact.old.subject|default:''}">
                                <span class="field-error">{$contact.errors.subject|default:''}</span>
                            </div>
                        </div>
                        <div class="field">
                            <label for="cf-message"><span class="msg-only">Nachricht *</span><span class="res-only res-label">Anmerkungen</span> <span class="res-only">(optional, z. B. Kinderstuhl, Anlass, Allergien)</span></label>
                            <textarea id="cf-message" name="message" rows="5" maxlength="5000">{$contact.old.message|default:''}</textarea>
                            <span class="field-error">{$contact.errors.message|default:''}</span>
                        </div>
                        <div class="field field-check">
                            <label><input type="checkbox" name="privacy" value="1" required>
                                {$site.form_consent|default:'Ich stimme zu, dass meine Angaben zur Bearbeitung meiner Anfrage gespeichert und verarbeitet werden.'} *</label>
                            <span class="field-error">{$contact.errors.privacy|default:''}</span>
                        </div>
                        <button type="submit" class="btn btn-primary"><span class="msg-only">Nachricht senden</span><span class="res-only">Reservierung anfragen</span></button>
                        <p class="form-hint">* Pflichtfelder.{if $site.form_hint|default:''} {$site.form_hint|with_phone:$site.phone nofilter}{/if}</p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <p>{$site.footer_text}</p>
        <p>&copy; {$year} {$site.site_name} · <a href="impressum.php">Impressum</a> · <a href="datenschutz.php">Datenschutz</a></p>
    </div>
</footer>

<a href="#start" class="to-top" aria-label="Nach oben">&uarr;</a>

<script src="{'assets/vendor/jquery-3.7.1.min.js'|asset}"></script>
<script src="{'assets/js/main.js'|asset}"></script>
</body>
</html>
