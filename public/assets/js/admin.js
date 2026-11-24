/* Backend-Skripte (jQuery) */
(function ($) {
    'use strict';

    // CSRF-Token bei allen AJAX-Anfragen mitsenden
    $.ajaxSetup({
        headers: { 'X-CSRF-Token': window.CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
    });

    function toast(msg) {
        var $t = $('.toast');
        if (!$t.length) { $t = $('<div class="toast" role="status"></div>').appendTo('body'); }
        $t.text(msg).stop(true).fadeIn(150).delay(1800).fadeOut(300);
    }

    function ajaxError(xhr) {
        var res = xhr.responseJSON || {};
        toast(res.message || 'Aktion fehlgeschlagen. Bitte Seite neu laden.');
    }

    /* Mobile Navigation */
    $('.admin-nav-toggle').on('click', function () {
        var open = $('#admin-nav').toggleClass('is-open').hasClass('is-open');
        $(this).attr('aria-expanded', open);
    });

    /* Sicherheitsabfrage vor dem Löschen */
    $(document).on('submit', 'form[data-confirm]', function (e) {
        if (!window.confirm($(this).data('confirm'))) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    /* Gerichte per AJAX löschen */
    $(document).on('submit', 'form.js-ajax-delete', function (e) {
        e.preventDefault();
        var $form = $(this);
        $.post($form.attr('action'), $form.serialize()).done(function () {
            $form.closest('.item-row').fadeOut(200, function () { $(this).remove(); });
            toast('Gelöscht.');
        }).fail(ajaxError);
    });

    /* Gericht ein-/ausblenden */
    $(document).on('change', '.js-toggle-item', function () {
        var $cb = $(this);
        $.post('index.php?page=item_toggle', { id: $cb.data('id') }).done(function (res) {
            $cb.prop('checked', !!res.active);
            $cb.closest('.item-row').toggleClass('is-inactive', !res.active);
            toast(res.active ? 'Gericht wird angezeigt.' : 'Gericht ist ausgeblendet.');
        }).fail(function (xhr) {
            $cb.prop('checked', !$cb.prop('checked'));
            ajaxError(xhr);
        });
    });

    /* Bannerbild ein-/ausblenden */
    $(document).on('change', '.js-toggle-slide', function () {
        var $cb = $(this);
        $.post('index.php?page=slide_toggle', { id: $cb.data('id') }).done(function (res) {
            $cb.prop('checked', !!res.active);
            $cb.closest('.slide-row').toggleClass('is-inactive', !res.active);
            toast(res.active ? 'Bild wird im Banner gezeigt.' : 'Bild ist ausgeblendet.');
        }).fail(function (xhr) {
            $cb.prop('checked', !$cb.prop('checked'));
            ajaxError(xhr);
        });
    });

    /* Bildausschnitt: Vorschau sofort anpassen */
    $(document).on('change', '.js-slide-position', function () {
        $(this).closest('.slide-row').find('.slide-thumb').css('background-position', $(this).val());
    });

    /* Bannerbild-Angaben per AJAX speichern */
    $(document).on('submit', '.js-slide-form', function (e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize())
            .done(function () { toast('Gespeichert.'); })
            .fail(ajaxError);
    });

        /* Öffnungszeiten: Ruhetag graut die Uhrzeiten aus */
    $(document).on('change', '.js-closed', function () {
        $(this).closest('.js-day').toggleClass('is-closed', this.checked);
    });

    /* Ausnahme: Uhrzeiten nur bei „geöffnet“ / „geänderte Zeiten“ */
    function toggleExcTimes() {
        var type = $('.js-exc-type:checked').val();
        $('.js-exc-times').toggle(type !== 'closed');
    }
    $(document).on('change', '.js-exc-type', toggleExcTimes);
    toggleExcTimes();

        /* Bildvorschau vor dem Upload */
    $('input[type=file][data-preview-target]').on('change', function () {
        var file = this.files && this.files[0];
        var $box = $('[data-preview="' + $(this).data('preview-target') + '"]');
        if (!file || !/^image\//.test(file.type)) { return; }
        var reader = new FileReader();
        reader.onload = function (ev) { $box.empty().append($('<img alt="">').attr('src', ev.target.result)); };
        reader.readAsDataURL(file);
    });

    /* Preisvarianten hinzufügen/entfernen */
    $('#add-variant').on('click', function () {
        var tpl = document.getElementById('variant-template');
        var $row = $(tpl.content.cloneNode(true)).children().first();
        $('#variants').append($row);
        $row.find('input').first().trigger('focus');
    });
    $(document).on('click', '.js-remove-variant', function () {
        $(this).closest('.variant-row').remove();
    });

    /* Drag & Drop-Sortierung (HTML5 Drag and Drop, über den Griff ⠿) */
    var $dragged = null;

    $(document).on('dragstart', '.handle', function (e) {
        $dragged = $(this).closest('[data-id]');
        $dragged.addClass('is-dragging');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/plain', $dragged.data('id'));
        if (e.originalEvent.dataTransfer.setDragImage) {
            e.originalEvent.dataTransfer.setDragImage($dragged[0], 20, 20);
        }
    });

    $(document).on('dragover', '.sortable > [data-id]', function (e) {
        if (!$dragged) { return; }
        var $target = $(this);
        // Nur innerhalb derselben Liste sortieren
        if ($target.parent()[0] !== $dragged.parent()[0] || $target[0] === $dragged[0]) { return; }
        e.preventDefault();
        e.stopPropagation();
        var rect = this.getBoundingClientRect();
        var after = e.originalEvent.clientY > rect.top + rect.height / 2;
        $('.drop-before, .drop-after').removeClass('drop-before drop-after');
        $target.addClass(after ? 'drop-after' : 'drop-before');
    });

    $(document).on('drop', '.sortable > [data-id]', function (e) {
        if (!$dragged) { return; }
        var $target = $(this);
        if ($target.parent()[0] !== $dragged.parent()[0]) { return; }
        e.preventDefault();
        e.stopPropagation();
        if ($target.hasClass('drop-after')) { $target.after($dragged); } else { $target.before($dragged); }
        saveOrder($dragged.parent());
    });

    $(document).on('dragend', '.handle', function () {
        $('.drop-before, .drop-after').removeClass('drop-before drop-after');
        if ($dragged) { $dragged.removeClass('is-dragging'); }
        $dragged = null;
    });

    function saveOrder($list) {
        var ids = $list.children('[data-id]').map(function () { return $(this).data('id'); }).get();
        $.post('index.php?page=reorder', { type: $list.data('sort-type'), ids: ids })
            .done(function () { toast('Reihenfolge gespeichert.'); })
            .fail(ajaxError);
    }
})(jQuery);
