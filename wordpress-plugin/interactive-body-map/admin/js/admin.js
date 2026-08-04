/**
 * Settings screen behaviour: colour pickers that update the preview live,
 * and a copy button for the shortcode.
 */
(function ($) {
    'use strict';

    var VARS = {
        fill: '--bodymap-fill',
        hover: '--bodymap-fill-hover',
        active: '--bodymap-fill-active',
        disabled: '--bodymap-fill-disabled',
        stroke: '--bodymap-stroke',
        detail: '--bodymap-detail',
        tooltip_bg: '--bodymap-tooltip-bg',
        tooltip_text: '--bodymap-tooltip-color'
    };

    $(function () {
        var preview = document.querySelector('#bodymap-preview .bodymap');

        function apply(field, value) {
            if (!preview) { return; }
            var name = VARS[$(field).data('bodymap-var')];
            if (!name) { return; }

            if (value) {
                preview.style.setProperty(name, value);
            } else {
                preview.style.removeProperty(name);
            }
        }

        $('.bodymap-colour-field').wpColorPicker({
            change: function (event, ui) {
                apply(this, ui.color.toString());
            },
            clear: function () {
                apply(this, '');
            }
        });

        // The preview should react to the mode too, but re-rendering it needs a
        // round trip; a note is friendlier than a stale figure.
        $('#bodymap-mode').on('change', function () {
            $('#bodymap-preview').toggleClass('is-stale', true);
        });

        $('#bodymap-max-width').on('input', function () {
            if (preview) { preview.style.maxWidth = this.value; }
        });

        $('.bodymap-copy').on('click', function () {
            var button = this;
            var text = $(button).data('clipboard');

            var done = function () {
                var original = button.textContent;
                button.textContent = (window.BODYMAP_ADMIN && BODYMAP_ADMIN.copied) || 'Copied';
                setTimeout(function () { button.textContent = original; }, 1600);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done, function () {});
                return;
            }

            var field = $('<textarea>').val(text).css({ position: 'fixed', opacity: 0 }).appendTo('body');
            field[0].select();
            try { document.execCommand('copy'); done(); } catch (e) { /* nothing useful to do */ }
            field.remove();
        });
    });
}(jQuery));
