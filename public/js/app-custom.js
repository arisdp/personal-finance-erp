$(document).ready(function() {
    // Global selector for all numeric inputs
    $('.numeric-input').each(function() {
        const input = $(this);
        const targetId = input.attr('id').replace('_display', '_hidden');
        const targetInput = $('#' + targetId);

        if (!targetInput.length) {
            console.warn('AutoNumeric: No hidden target found for', input.attr('id'));
            return;
        }

        const an = new AutoNumeric(this, {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            minimumValue: '0',
            unformatOnSubmit: true
        });

        // Sync with hidden field on change
        input.on('change', function() {
            targetInput.val(an.getNumericString());
        });

        // Initial sync if editing
        if (targetInput.val()) {
            an.set(targetInput.val());
        }
    });

    // Handle Select2 global theme
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
});
