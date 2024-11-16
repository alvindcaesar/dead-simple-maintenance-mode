jQuery(document).ready(function ($) {
    var pageField = $('.page-selector-row');
    pageField.toggleClass('hidden-field', !$('#dsmm_activate').is(':checked'));
    $('.dsmm-checkbox-wrapper input[type="checkbox"]').on('change', function () {
        pageField.toggleClass('hidden-field', !$(this).is(':checked'));
    });
});