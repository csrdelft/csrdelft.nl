
jQuery(function(){

    //user

    /**
     *
     * /tools/naamsuggesties/$zoekin
     *  $zoekin = array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies')
     *
     * Toelichting op options voor RemoteSuggestions
     * result = array(
     *        array(data:array(..,..,..), value: "string", result:"string"),
     *         array(... )
     * )
     * formatItem geneert html-items voor de suggestielijst, afstemmen op data-array
     */
    var options = {
        dataType: 'json',
        parse: function (result) {
            return result;
        },
        formatItem: function (row, i, n, value) {
            return value;
        },
        clickFire: true,
        multipleSeparator: ", ",
        max: 20,
        minChars: 2
    };


    jQuery('input.lidsuggesties').each(function (index, tag) {
        jQuery(tag).autocomplete(
            '/tools/naamsuggesties/'+jQuery(tag).data('zoekin'), jQuery.extend({}, options, {
                multiple: jQuery(this).hasClass('multiple'),
                formatItem: function (row, i, n) {
                    return row[0];
                },
                extraParams: {
                    result: 'uid'
                }
            })
        ).result(function () {
                jQuery(this).keyup();
            });
    });


    //boek
    jQuery('input.boeksuggesties').each(function (index, tag) {
        jQuery(tag).autocomplete(
            '/tools/suggesties/boek', jQuery.extend({}, options, {
                multiple: jQuery(this).hasClass('multiple'),
                formatItem: function (row, i, n, value) {
                    return row.titel + ' (<i>' + row.auteur + '</i>)';
                }
            })
        ).result(function () {
                jQuery(this).keyup();
            });
    });


    //document
    jQuery('input.documentsuggesties').each(function (index, tag) {
        jQuery(tag).autocomplete(
            '/tools/suggesties/document', jQuery.extend({}, options, {
                multiple: jQuery(this).hasClass('multiple'),
                formatItem: function (row, i, n, value) {
                    return row.naam + ' (<i>' + row.bestandsnaam + '</i>)';
                },
                extraParams: {
                    categorie: (jQuery(tag).data('categorie') ? jQuery(tag).data('categorie') : 0)
                }
            })
        ).result(function () {
                jQuery(this).keyup();
            });
    });


    //groep
    jQuery('input.groepsuggesties').each(function (index, tag) {
        jQuery(tag).autocomplete(
            '/tools/suggesties/groep', jQuery.extend({}, options, {
                multiple: jQuery(this).hasClass('multiple'),
                formatItem: function (row, i, n, value) {
                    return row.naam + ' (<i>' + row.status + ' ' + row.type + ' ' + row.snaam + '</i>)';
                },
                extraParams: {
                    type: (jQuery(tag).data('type') ? jQuery(tag).data('type') : 0)
                }
            })
        ).result(function () {
                jQuery(this).keyup();
            });
    });

});

