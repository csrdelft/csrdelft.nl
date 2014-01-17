jQuery(function () {
    var $searchform__input = jQuery('#qsearch2__in');

    $searchform__input.dw_qsearch({
        output_id: '#qsearch2__out',
        getSearchterm: function() {
            var sf_ns = jQuery('#searchform__ns').val();
            return $searchform__input.val() + (sf_ns ? ' @' + sf_ns : '');
        }
    });
});