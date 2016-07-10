(function ($, window, document)
{
    'use strict';

    $(function()
    {
        // Rich Text Editor
        $('[data-type=rte]').ckeditor();

        // Toggle
        $('[data-toggle]').on('change', function()
        {
            var $this = $(this);
            $('[data-toggle-id=' + $this.data('toggle') + ']').prop('checked', $this.prop('checked'));

        });
    });
})(jQuery, window, document);
