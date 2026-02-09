jQuery(document).ready(function($){
    $('.spice-toggle').on('change', function(){

        const key = $(this).data('key');
        const value = $(this).is(':checked');

        // Update toggle text
        $(this).siblings('.toggle-text').text(value ? 'On' : 'Off');

        // Save via AJAX
        $.post(ajaxurl, {
            action: 'spice_save_block_toggle',
            key: key,
            value: value ? 'true' : 'false',
        }, function(response){
            if(response.success){
                console.log(key + ' saved as ' + value);
            }
        });

    });
});
