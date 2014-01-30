/**
 * Add button action for the indexmenu wizard button
 *
 * @param  {jQuery}   $btn  Button element to add the action to
 * @param  {Array}    props Associative array of button properties
 * @param  {string}   edid  ID of the editor textarea
 * @return {string|Boolean}  If button should be appended
 */
function addBtnActionBoekwiz($btn, props, edid) {
    var csrlinkBoek = jQuery('#' + edid).csrLink({
        deferInit: true,
        id: 'boek__wiz',
        strings: {
            title: 'Boek invoegen',
            zoekin: 'Zoek boek'
        },
        props: props,
        set: 'boek'
    });

    csrlinkBoek.init();
    $btn.click(function () {
        csrlinkBoek.toggle();
        return false;
    });
    return 'boek__wiz';
}

/**
 * Add button action for the indexmenu wizard button
 *
 * @param  {jQuery}   $btn  Button element to add the action to
 * @param  {Array}    props Associative array of button properties
 * @param  {string}   edid  ID of the editor textarea
 * @return {string|Boolean}  If button should be appended
 */
function addBtnActionLidwiz($btn, props, edid) {
    var csrlinkLid = jQuery('#' + edid).csrLink({
        deferInit: true,
        id: 'lid__wiz',
        strings: {
            title: 'Lid invoegen',
            zoekin: 'Zoek lid'
        },
        props: props,
        set: 'lid',
        cleanInput: function (value) {
            return value.substr(0, 4);
        }
    });

    csrlinkLid.init();
    $btn.click(function () {
        csrlinkLid.toggle();
        return false;
    });
    return 'lid__wiz';
}

/**
 * Add button action for the indexmenu wizard button
 *
 * @param  {jQuery}   $btn  Button element to add the action to
 * @param  {Array}    props Associative array of button properties
 * @param  {string}   edid  ID of the editor textarea
 * @return {string|Boolean}  If button should be appended
 */
function addBtnActionGroepwiz($btn, props, edid) {
    var csrlinkGroep = jQuery('#' + edid).csrLink({
        deferInit: true,
        id: 'groep__wiz',
        strings: {
            title: 'Groep invoegen',
            zoekin: 'Zoek groep'
        },
        props: props,
        set: 'groep'
    });

    csrlinkGroep.init();
    $btn.click(function () {
        csrlinkGroep.toggle();
        return false;
    });
    return 'groep__wiz';
}

/**
 * Add button action for the indexmenu wizard button
 *
 * @param  {jQuery}   $btn  Button element to add the action to
 * @param  {Array}    props Associative array of button properties
 * @param  {string}   edid  ID of the editor textarea
 * @return {string|Boolean}  If button should be appended
 */
function addBtnActionDocumentwiz($btn, props, edid) {
    var csrlinkDoc = jQuery('#' + edid).csrLink({
        deferInit: true,
        id: 'doc__wiz',
        strings: {
            title: 'Document invoegen',
            zoekin: 'Zoek document'
        },
        props: props,
        set: 'document'
    });

    csrlinkDoc.init();
    $btn.click(function () {
        csrlinkDoc.toggle();
        return false;
    });
    return 'doc__wiz';
}


// try to add button to toolbar
if (window.toolbar != undefined) {
    window.toolbar[window.toolbar.length] = {
        type: "Boekwiz",
        title: "Een boek-verwijzing invoegen",
        icon: "../../plugins/csrlink/book.png",
        open: '[[boek>',
        close: ']]'
    };
    window.toolbar[window.toolbar.length] = {
        type: "Documentwiz",
        title: "Een document-verwijzing invoegen",
        icon: "../../plugins/csrlink/document.png",
        open: '[[document>',
        close: ']]'
    };
    window.toolbar[window.toolbar.length] = {
        type: "Lidwiz",
        title: "Een Lid invoegen",
        icon: "../../plugins/csrlink/user.png",
        open: '[[lid>',
        close: ']]'
    };
    window.toolbar[window.toolbar.length] = {
        type: "Groepwiz",
        title: "Een Groep-verwijzing invoegen",
        icon: "../../plugins/csrlink/group.png",
        open: '[[groep>',
        close: ']]'
    };
}

/**
 * Wizard die invoerveld met invoegknop weergeeft.
 * Bij invoegen wordt respectievelijke syntax in textarea geplaatst.
 *
 * @param {Object} overrides Object met waardes die bestaande onderdelen aanvullen of vervangen
 * @returns {Object} CSRLink object
 */
jQuery.fn.csrLink = function (overrides) {
    var CSRLink = {

        $wiz: null,
        $entry: null,
        $textArea: this,
        selection: null,
        id: '',
        strings: {
            title: '',
            zoekin: ''
        },

        /**
         * Draws dialog and add its event handlers
         */
        init: function () {
            // position relative to the text area
            var pos = CSRLink.$textArea.position();

            // create HTML Structure
            CSRLink.$wiz = jQuery(document.createElement('div'))
                .dialog({
                    autoOpen: false,
                    draggable: true,
                    title: CSRLink.strings.title,
                    resizable: false
                })
                .html(
                    '<fieldset class="invoer">' +
                        '<label for="csrlink_' + CSRLink.id + '_entry">' +
                        CSRLink.strings.zoekin + ': ' +
                        '</label>' +
                        '<input type="text" class="edit" id="csrlink_' + CSRLink.id + '_entry" />' +
                        '<input type="submit" value="Invoegen" class="button" />' +
                        '</fieldset>'
                )
                .parent()
                .attr('id', CSRLink.id)
                .addClass('csrlink__wiz')
                .css({
                    'position': 'absolute',
                    'top': (pos.top + 20) + 'px',
                    'left': (pos.left + 80) + 'px'
                })
                .hide()
                .appendTo('.dokuwiki:first');

            CSRLink.$wiz.find('input.button').click(CSRLink.insertItem);

            CSRLink.$entry = jQuery('input#csrlink_' + CSRLink.id + '_entry');
            CSRLink.$entry.keyup(CSRLink.onEntry);

            //init autocomplete on a input
            CSRAutocomplete.initAutocomplete(CSRLink.$entry[0], function () {
            }, CSRAutocomplete.props[CSRLink.set]);

            CSRLink.$wiz.find('.ui-dialog-titlebar-close').click(CSRLink.hide);
        },

        /**
         * handle all keyup events in the entry field
         *
         * @param e event
         * @returns {boolean}
         */
        onEntry: function (e) {
            if (e.keyCode == 27) { //Escape
                CSRLink.hide();
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            if (e.keyCode == 13) { //Enter
                CSRLink.insertItem();
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        },

        /**
         * Create syntax and close dialog
         */
        insertItem: function () {
            var itemid = CSRLink.cleanInput(CSRLink.$entry.val()),
                sel, stxt;
            if (!itemid) {
                return;
            }

            sel = getSelection(CSRLink.$textArea[0]);
            if (sel.start == 0 && sel.end == 0) {
                sel = CSRLink.selection;
            }

            stxt = sel.getText();

            // don't include trailing space in selection
            if (stxt.charAt(stxt.length - 1) == ' ') {
                sel.end--;
                stxt = sel.getText();
            }

            var so = itemid.length;
            var eo = 0;
            if (CSRLink.props) {
                if (CSRLink.props.open) {
                    so += CSRLink.props.open.length;
                    itemid = CSRLink.props.open + itemid;
                }
                if (stxt) {
                    itemid += '|' + stxt;
                    so += 1;
                }
                if (CSRLink.props.close) {
                    itemid += CSRLink.props.close;
                    eo = CSRLink.props.close.length;
                }
            }

            pasteText(sel, itemid, {startofs: so, endofs: eo});
            CSRLink.hide();
        },

        /**
         * Clean input value
         *
         * @param {*} value
         * @returns {Number}
         */
        cleanInput: function (value) {
            return parseInt(value, 10);
        },

        /**
         * Toggle the link wizard
         */
        toggle: function () {
            if (CSRLink.$wiz.css('display') == 'none') {
                CSRLink.show();
            } else {
                CSRLink.hide();
            }
        },
        /**
         * Show wizard
         */
        show: function () {
            CSRLink.selection = getSelection(CSRLink.$textArea[0]);
            CSRLink.$wiz.show();
            CSRLink.$entry.focus();
        },
        /**
         * Hide wizard
         */
        hide: function () {
            CSRLink.$wiz.hide();
            CSRLink.$textArea.focus();
        }
    };

    jQuery.extend(CSRLink, overrides);

    if (!overrides.deferInit) {
        CSRLink.init();
    }

    return CSRLink;
};