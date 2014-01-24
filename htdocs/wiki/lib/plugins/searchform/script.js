jQuery(function () {
    jQuery('.searchform__qsearch_in')
        .each(function (i, input) {
            var $input = jQuery(input);
            var $form = $input.parent().parent();
            var $output = $form.find('.searchform__qsearch_out');
            var $ns = $form.find('.searchform__ns');

            $input.dw_qsearch({
                output: $output,
                getSearchterm: function () {
                    var namespace = $ns.val();
                    return $input.val() + (namespace ? ' @' + namespace : '');
                }
            });

        });
});