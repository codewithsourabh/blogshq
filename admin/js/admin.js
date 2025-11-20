(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color picker
        if ($.fn.wpColorPicker) {
            $('.blogshq-color-picker').wpColorPicker();
        }

        // Add any custom admin JavaScript here
    });

})(jQuery);