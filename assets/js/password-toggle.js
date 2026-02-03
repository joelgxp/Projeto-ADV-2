/**
 * Toggle visibilidade da senha - ícone de olho
 */
(function($) {
    $(document).ready(function() {
        $(document).on('click', '.pwd-toggle-icon', function(e) {
            e.preventDefault();
            var $icon = $(this);
            var $input = $icon.siblings('input[type="password"], input[type="text"]').first();
            if ($input.length) {
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $icon.removeClass('bx-show-alt').addClass('bx-hide');
                    $icon.attr('title', 'Ocultar senha');
                } else {
                    $input.attr('type', 'password');
                    $icon.removeClass('bx-hide').addClass('bx-show-alt');
                    $icon.attr('title', 'Mostrar senha');
                }
            }
        });
    });
})(jQuery);
