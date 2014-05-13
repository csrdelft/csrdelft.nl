jQuery('form.plugin_move_form').each(function(){
    var $form = jQuery(this);

    $form.find('.click-page').click(function() {
        $form.find('input[name=dst]').val($form.find('.click-page code').text());
        $form.find('.select').hide();
    }).click();

    $form.find('.click-ns').click(function() {
        $form.find('input[name=dst]').val($form.find('.click-ns code').text());
        $form.find('.select').show();
    });

});