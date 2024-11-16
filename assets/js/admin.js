jQuery(document).ready(function ($) {
    var pageField = $('.page-selector-row');

    // Set initial state
    pageField.toggleClass('hidden-field', !$('#dsmm_activate').is(':checked'));

    // Handle changes
    $('#dsmm_activate').on('change', function () {
        pageField.toggleClass('hidden-field', !$(this).is(':checked'));
    });
});