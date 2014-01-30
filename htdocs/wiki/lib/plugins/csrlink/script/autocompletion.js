/**
 * Adds suggestions from csrdelft.nl to inputs in the wiki
 */
var CSRAutocomplete = {
    props: {
        groep: {
            datatype: 'groep',
            dataRow: function (row, i) {
                //row: id, type, snaam, naam, status
                return {
                    label: row.naam + ' (' + row.status + ' ' + row.type + ' - ' + row.snaam + ')',
                    value: row.id
                }
            }
        },
        lid: {
            datatype: 'lid',
            dataRow: function (row, i) {
                console.log(row);
                //row: data[fullname,uid], result, value
                return {
                    label: row.data[0] + ' (' + row.data[1] + ')', //fullname + uid
                    value: '' + row.result //uid
                }
            },
            url: '/tools/naamsuggesties.php'
        },
        document: {
            datatype: 'document',
            dataRow: function (row, i) {
                //row: naam, bestandsnaam, id
                return {
                    label: row.naam + ' (' + row.bestandsnaam + ')',
                    value: row.id
                }
            }
        },
        boek: {
            datatype: 'boek',
            dataRow: function (row, i) {
                //row: titel, auteur, id
                return {
                    label: row.titel + ' (' + row.auteur + ')',
                    value: row.id
                }
            }

        }
    },

    split: function (val) {
        return val.split(/,\s*/);
    },

    extractLast: function (term) {
        return CSRAutocomplete.split(term).pop();
    },

    // id, type, snaam, naam, status
    remotesource: function (request, response, getTerm, props) {
        jQuery.getJSON(
            props.url,
            {
                datatype: props.datatype,
                q: getTerm(request),
                limit: 20,
                type: props.type, //only groep
                result: 'uid', //only lid
                zoekin: props.zoekin, //only lid
                categorie: props.categorie //only documenten
            },
            function (data) {
                if (data.length && data[0].error) {
                    response([
                        {
                            label: data[0].error,
                            value: ''
                        }
                    ]);
                } else {
                    response(jQuery.map(data, props.dataRow));
                }
            }
        );
    },


    initAutocomplete: function (input, resulthandler, props) {
        var $input = jQuery(input);

        props = jQuery.extend({}, {
            url: '/tools/suggesties.php',
            type: $input.data('type') || 0,
            multiple: $input.hasClass('multiple'),
            categorie: $input.data('categorie') || 0,
            zoekin: $input.data('zoekin') || ''
        }, props);


        var options = {
            source: function (request, response) {
                function getTerm(req) {
                    return req.term;
                }

                CSRAutocomplete.remotesource(request, response, getTerm, props);
            },
            change: resulthandler
        };

        var multipleoptions = {
            minLength: 0,
            source: function (request, response) {
                function getTerm(req) {
                    return CSRAutocomplete.extractLast(req.term);
                }

                CSRAutocomplete.remotesource(request, response, getTerm, props);
            },
            search: function () {
                // custom minLength
                var term = CSRAutocomplete.extractLast(this.value);
                return term.length >= 2;
            },
            focus: function () {
                // prevent value inserted on focus
                return false;
            },
            select: function (event, ui) {
                var terms = CSRAutocomplete.split(this.value);
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push(ui.item.value);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        };
        if (props.multiple) {
            options = jQuery.extend({}, options, multipleoptions);
        }

        $input.autocomplete(options);
    }
};

function triggerFieldset() {
    //trigger depending fieldset
    jQuery(this).keyup();
}


//add to inputs
jQuery(function () {
    jQuery('input.lidsuggesties').each(function (index, input) {
        CSRAutocomplete.initAutocomplete(input, triggerFieldset, CSRAutocomplete.props.lid)
    });

    jQuery('input.boeksuggesties').each(function (index, input) {
        CSRAutocomplete.initAutocomplete(input, triggerFieldset, CSRAutocomplete.props.boek)
    });

    jQuery('input.documentsuggesties').each(function (index, input) {
        CSRAutocomplete.initAutocomplete(input, triggerFieldset, CSRAutocomplete.props.document)
    });

    jQuery('input.groepsuggesties').each(function (index, input) {
        CSRAutocomplete.initAutocomplete(input, triggerFieldset, CSRAutocomplete.props.groep)
    });
});
