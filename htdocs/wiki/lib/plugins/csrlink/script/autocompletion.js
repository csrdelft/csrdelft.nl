
var autocompleteoptions = {
    dataType: 'json',
    parse: function (result) {
        return result;
    },
    formatItem: function (row, i, n, value) {
        return value;
    },
    clickFire: true,
    multipleSeparator: ", ",
    max: 30,
    minChars: 2
};

function triggerFieldset() {
    //trigger depending fieldset
    jQuery(this).keyup();
}

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
function initLidsuggesties (input, resulthandler) {
    jQuery(input).autocomplete(
        '/tools/naamsuggesties/' + jQuery(input).data('zoekin'),
        jQuery.extend({}, autocompleteoptions, {
            multiple: jQuery(input).hasClass('multiple'),
            formatItem: function (row, i, n) {
                return row[0];
            },
            extraParams: {
                result: 'uid'
            }
        })
    ).result(resulthandler);
}


//boek
function initBoeksuggesties (input, resulthandler) {
    jQuery(input).autocomplete(
        '/tools/suggesties/boek',
        jQuery.extend({}, autocompleteoptions, {
            multiple: jQuery(input).hasClass('multiple'),
            formatItem: function (row, i, n, value) {
                return row.titel + ' (<i>' + row.auteur + '</i>)';
            }
        })
    ).result(resulthandler);
}


//document
function initDocumentsuggesties (input, resulthandler) {
    jQuery(input).autocomplete(
        '/tools/suggesties/document',
        jQuery.extend({}, autocompleteoptions, {
            multiple: jQuery(input).hasClass('multiple'),
            formatItem: function (row, i, n, value) {
                return row.naam + ' (<i>' + row.bestandsnaam + '</i>)';
            },
            extraParams: {
                categorie: jQuery(input).data('categorie') || 0
            }
        })
    ).result(resulthandler);
}


//groep
function initGroepsuggesties (input, resulthandler) {
    jQuery(input).autocomplete(
        '/tools/suggesties/groep',
        jQuery.extend({}, autocompleteoptions, {
            multiple: jQuery(input).hasClass('multiple'),
            formatItem: function (row, i, n, value) {
                return row.naam + ' (<i>' + row.status + ' ' + row.type + ' ' + row.snaam + '</i>)';
            },
            extraParams: {
                type: jQuery(input).data('type') || 0
            }
        })
    ).result(resulthandler);
}

//add to inputs
jQuery(function () {
    jQuery('input.lidsuggesties').each(function (index, input) {
        initLidsuggesties(input, triggerFieldset);
    });

    jQuery('input.boeksuggesties').each(function (index, input) {
        initBoeksuggesties(input, triggerFieldset);
    });

    jQuery('input.documentsuggesties').each(function (index, input) {
        initDocumentsuggesties(input, triggerFieldset);
    });

    jQuery('input.groepsuggesties').each(function (index, input) {
        initGroepsuggesties(input, triggerFieldset);
    });
});
