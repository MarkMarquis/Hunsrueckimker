/* Webseiten-Vorlage – Frontend-Skripte (jQuery) */
(function ($) {
    'use strict';
    document.documentElement.classList.remove('no-js');

    var $win = $(window);
    var $header = $('.site-header');
    var $nav = $('#main-nav');
    var $toggle = $('.nav-toggle');
    var headerH = function () { return $header.outerHeight() || 0; };

    /* ---------- Header beim Scrollen einfärben, „Nach oben“-Button ---------- */
    function onScroll() {
        var y = $win.scrollTop();
        $header.toggleClass('is-solid', y > 40);
        $('.to-top').toggleClass('is-visible', y > 600);
        highlightNav(y);
    }

    /* ---------- Aktiven Menüpunkt hervorheben ---------- */
    var $navLinks = $nav.find('a[href^="#"]');
    function highlightNav(y) {
        var current = null;
        $navLinks.each(function () {
            var $sec = $($(this).attr('href'));
            if ($sec.length && $sec.offset().top - headerH() - 80 <= y) {
                current = this;
            }
        });
        $navLinks.removeClass('is-active');
        if (current) { $(current).addClass('is-active'); }
    }

    $win.on('scroll', onScroll);
    onScroll();

    /* ---------- Mobile Navigation ---------- */
    $toggle.on('click', function () {
        var open = !$nav.hasClass('is-open');
        $nav.toggleClass('is-open', open);
        $toggle.attr('aria-expanded', open);
    });

    /* ---------- Sanftes Scrollen zu Ankern ---------- */
    $(document).on('click', 'a[href^="#"]', function (e) {
        var target = $(this).attr('href');
        var $target = target.length > 1 ? $(target) : $();
        if (!$target.length) { return; }
        e.preventDefault();
        $nav.removeClass('is-open');
        $toggle.attr('aria-expanded', 'false');
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        $('html, body').stop().animate({ scrollTop: $target.offset().top - headerH() + 1 }, reduce ? 0 : 500);
        if (history.replaceState) { history.replaceState(null, '', target); }
    });

    /* ---------- Speisekarte: Kategorien als Tabs ---------- */
    var $tabs = $('.menu-tab');
    $tabs.on('click', function () {
        var $tab = $(this);
        $tabs.removeClass('is-active').attr('aria-selected', 'false');
        $tab.addClass('is-active').attr('aria-selected', 'true');
        $('.menu-category').removeClass('is-active');
        $('#' + $tab.attr('aria-controls')).addClass('is-active');
    }).on('keydown', function (e) {
        var i = $tabs.index(this);
        if (e.key === 'ArrowRight') { $tabs.eq((i + 1) % $tabs.length).trigger('focus').trigger('click'); }
        if (e.key === 'ArrowLeft') { $tabs.eq((i - 1 + $tabs.length) % $tabs.length).trigger('focus').trigger('click'); }
    });

    /* ---------- News: lange Texte kürzen, ältere nachladen ---------- */
    $('.news-text').each(function () {
        var $text = $(this);
        if (this.scrollHeight > 160) {
            $text.addClass('is-clamped');
            $text.siblings('.news-more').prop('hidden', false);
        }
    });
    $('.news-more').on('click', function () {
        var $text = $(this).siblings('.news-text');
        var clamped = $text.toggleClass('is-clamped').hasClass('is-clamped');
        $(this).text(clamped ? 'Weiterlesen' : 'Weniger anzeigen');
    });
    $('#news-load-more').on('click', function () {
        var $hidden = $('.news-card.is-hidden');
        $hidden.slice(0, 3).removeClass('is-hidden').hide().fadeIn(300);
        if ($hidden.length <= 3) { $(this).parent().remove(); }
    });

    /* ---------- Bildwechsler im Banner ---------- */
    (function () {
        var $hero = $('#start');
        var $slides = $hero.find('.hero-slide');
        var $dots = $hero.find('.hero-dot');
        if ($slides.length < 2) { return; }

        var current = 0;
        var timer = null;
        var interval = 6500;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function show(index) {
            current = (index + $slides.length) % $slides.length;
            $slides.removeClass('is-active').attr('aria-hidden', 'true')
                .eq(current).addClass('is-active').removeAttr('aria-hidden');
            $dots.removeClass('is-active').removeAttr('aria-current')
                .eq(current).addClass('is-active').attr('aria-current', 'true');
        }
        function start() {
            if (reduceMotion) { return; }
            stop();
            timer = setInterval(function () { show(current + 1); }, interval);
        }
        function stop() {
            if (timer) { clearInterval(timer); timer = null; }
        }
        function goTo(index) { show(index); start(); }

        $hero.find('.hero-arrow--next').on('click', function () { goTo(current + 1); });
        $hero.find('.hero-arrow--prev').on('click', function () { goTo(current - 1); });
        $dots.on('click', function () { goTo($dots.index(this)); });

        // Pause, solange Maus oder Tastatur-Fokus im Banner ist
        $hero.on('mouseenter focusin', stop).on('mouseleave focusout', start);
        // Pause, wenn der Browser-Tab nicht sichtbar ist
        $(document).on('visibilitychange', function () { document.hidden ? stop() : start(); });

        // Wischen auf Touch-Geräten
        var touchX = null;
        $hero.on('touchstart', function (e) {
            touchX = e.originalEvent.touches[0].clientX;
        }).on('touchend', function (e) {
            if (touchX === null) { return; }
            var dx = e.originalEvent.changedTouches[0].clientX - touchX;
            if (Math.abs(dx) > 50) { goTo(current + (dx < 0 ? 1 : -1)); }
            touchX = null;
        });

        // Pfeiltasten, wenn ein Bedienelement des Banners fokussiert ist
        $hero.on('keydown', '.hero-dot, .hero-arrow', function (e) {
            if (e.key === 'ArrowRight') { goTo(current + 1); }
            if (e.key === 'ArrowLeft') { goTo(current - 1); }
        });

        start();
    })();

    /* ---------- Meldung im Banner: zur vollständigen Meldung springen ---------- */
    $('.hero-news').on('click', function () {
        var $card = $('#news-' + $(this).data('news-id'));
        if (!$card.length) { return; }
        $card.removeClass('is-hidden');
        var $text = $card.find('.news-text.is-clamped');
        if ($text.length) {
            $text.removeClass('is-clamped');
            $card.find('.news-more').text('Weniger anzeigen');
        }
        setTimeout(function () {
            $card.removeClass('is-highlight');
            void $card[0].offsetWidth;
            $card.addClass('is-highlight');
        }, 450);
    });

    /* ---------- Anfrage / Reservierung umschalten ---------- */
    var $cform = $('#contact-form');
    var $area = $('#cf-area');

    function isReservation() {
        return $cform.find('[name=type]:checked').val() === 'reservierung';
    }
    function setMode(reservation) {
        $cform.find('[name=type][value=' + (reservation ? 'reservierung' : 'anfrage') + ']').prop('checked', true);
        $cform.toggleClass('is-reservation', reservation);
    }
    // Bereiche mit Mindestpersonenzahl (2. OG ab 15) erst ab passender Gruppengröße freigeben
    function updateAreas() {
        var persons = parseInt($('#cf-persons').val(), 10) || 0;
        $area.find('option').each(function () {
            var min = parseInt($(this).data('min'), 10) || 0;
            $(this).prop('disabled', min > 0 && persons > 0 && persons < min);
        });
        if ($area.find('option:selected').prop('disabled')) { $area.val('egal'); }
    }
    $cform.on('change', '[name=type]', function () { setMode(isReservation()); });
    $('#cf-persons').on('input change', updateAreas);
    updateAreas();

    // „Tisch reservieren“ im Banner: Reservierung vorauswählen
    $('.js-reserve').on('click', function () {
        setMode(true);
        setTimeout(function () { $('#cf-date').trigger('focus'); }, 650);
    });
    if (location.hash === '#reservierung') { setMode(true); }

    /* ---------- Kontaktformular per AJAX ---------- */
    var $form = $('#contact-form');
    var $alert = $('.form-alert');

    function showAlert(ok, msg) {
        $alert.removeClass('is-success is-error')
            .addClass('is-visible ' + (ok ? 'is-success' : 'is-error'))
            .text(msg);
    }

    function clearErrors() {
        $form.find('.field').removeClass('has-error').find('.field-error').text('');
    }

    function setError(name, msg) {
        var $field = $form.find('[name="' + name + '"]').closest('.field');
        $field.addClass('has-error').find('.field-error').text(msg);
    }

    function validate() {
        var ok = true;
        var email = $.trim($form.find('[name=email]').val());
        if (!$.trim($form.find('[name=name]').val())) { setError('name', 'Bitte geben Sie Ihren Namen an.'); ok = false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setError('email', 'Bitte geben Sie eine gültige E-Mail-Adresse an.'); ok = false; }
        if (isReservation()) {
            var persons = parseInt($form.find('[name=persons]').val(), 10) || 0;
            var minArea = parseInt($area.find('option:selected').data('min'), 10) || 0;
            if (!$form.find('[name=res_date]').val()) { setError('res_date', 'Bitte wählen Sie ein Datum.'); ok = false; }
            if (!$form.find('[name=res_time]').val()) { setError('res_time', 'Bitte wählen Sie eine Uhrzeit.'); ok = false; }
            if (persons < 1) { setError('persons', 'Bitte geben Sie die Anzahl der Personen an.'); ok = false; }
            else if (persons < minArea) { setError('area', 'Dieser Bereich ist nur für Gruppen ab ' + minArea + ' Personen buchbar.'); ok = false; }
        } else if ($.trim($form.find('[name=message]').val()).length < 10) {
            setError('message', 'Bitte schreiben Sie eine Nachricht (mind. 10 Zeichen).'); ok = false;
        }
        if (!$form.find('[name=privacy]').is(':checked')) { setError('privacy', 'Bitte stimmen Sie der Verarbeitung Ihrer Daten zu.'); ok = false; }
        return ok;
    }

    $form.on('input change', '.has-error input, .has-error textarea', function () {
        $(this).closest('.field').removeClass('has-error').find('.field-error').text('');
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        clearErrors();
        if (!validate()) {
            showAlert(false, 'Bitte prüfen Sie Ihre Eingaben.');
            $form.find('.has-error').first().find('input, textarea').trigger('focus');
            return;
        }
        var $btn = $form.find('[type=submit]').prop('disabled', true).text('Wird gesendet …');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function (res) {
            showAlert(true, res.message);
            var wasReservation = isReservation();
            $form[0].reset();
            setMode(wasReservation);
            updateAreas();
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            $.each(res.errors || {}, setError);
            showAlert(false, res.message || 'Die Nachricht konnte nicht gesendet werden. Bitte versuchen Sie es später erneut.');
        }).always(function () {
            $btn.prop('disabled', false).html('<span class="msg-only">Nachricht senden</span><span class="res-only">Reservierung anfragen</span>');
        });
    });

    /* ---------- Einblend-Animation beim Scrollen ---------- */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('is-revealed');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        $('.section-title, .prose, .news-card, .menu-tabs, .contact-info, .contact-form-wrap').each(function () {
            $(this).addClass('reveal');
            io.observe(this);
        });
    }
})(jQuery);
