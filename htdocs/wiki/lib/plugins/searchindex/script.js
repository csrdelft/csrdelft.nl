/**
 * Javascript for searchindex manager plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Symon Bent <hendrybadao@gmail.com>
 *     Complete rewrite using jQuery and revealing module pattern
 *     Separate update and rebuild options
 */

var plugin_searchindex = (function() {

    // public methods/properties
    var pub = {};

    // private vars
    var pages = null,
        page =  null,
        url =  null,
        done =  1,
        count = 0,
        $msg = null,
        $buttons = null,
        lang = null;
        force = '';

    /**
     * initialize everything
     */
    pub.init = function() {
        $msg = jQuery('#plugin__searchindex_msg');
        if( ! $msg) return;

        lang = LANG.plugins.searchindex;
        url = DOKU_BASE + 'lib/plugins/searchindex/ajax.php';

        $buttons = jQuery('#plugin__searchindex_buttons');

        // init interface events
        jQuery('#plugin__searchindex_update').click(pub.update);
        jQuery('#plugin__searchindex_rebuild').click(pub.rebuild);
    };

    /**
     * Gives textual feedback
     */
    var message = function(text) {
        if (text.charAt(0) !== '<') {
            text = '<p>' + text + '</p>'
        }
        $msg.html(text);
    };

    /**
     * Starts the indexing of a page.
     */
    var index = function() {
        if (page) {
            jQuery.post(url, 'call=indexpage&page=' + encodeURI(page) + '&force=' + force, function(response) {
                var wait = 250;
                // next page from queue
                page = pages.shift();
                done++;

                var msg = (response !== 'true') ? lang.notindexed : lang.indexed;
                status = '<p class="status">' + msg + '</p>';
                message('<p>' + lang.indexing + ' ' + done + '/' + count + '</p><p class="name">' + page + '</p>' + status);
                // next index run
                window.setTimeout(index, wait);
            });
        } else {
            finished();
        }
    };

    var finished = function() {
        // we're done
        throbber_off();
        message(lang.done);
        window.setTimeout(function() {
            message('');
            $buttons.show('slow');
        }, 3000);
    };
    /**
     * Cleans the index (ready for complete rebuild)
     */
    var clear = function() {
        message(lang.clearing);
        jQuery.post(url, 'call=clearindex', function(response) {
            if (response !== 'true') {
                message(response);
                // retry
                window.setTimeout(clear,5000);
            } else {
                // start indexing
                force = 'true';
                window.setTimeout(index,1000);
            }
        });
    };

    pub.rebuild = function() {
        pub.update(true);
    };
    /**
     * Starts the index update
     */
    pub.update = function(rebuild) {
        done = 1;
        rebuild = rebuild || false;
        $buttons.hide('slow');
        throbber_on();
        message(lang.finding);
        jQuery.post(url, 'call=pagelist', function(response) {
            if (response !== 'true') {
                pages = response.split("\n");
                count = pages.length;
                message(lang.pages.replace(/%d/, pages.length));

                // move the first page from the queue
                page = pages.shift();

                // complete index rebuild?
                if (rebuild === true) {
                    clear();
                } else {
                    force = '';
                    // just start indexing immediately
                    window.setTimeout(index,1000);
                }
            } else {
                finished();
            }
        });
    };

    /**
     * add a throbber image
     */
    var throbber_on = function() {
        $msg.addClass('updating');
    };

    /**
     * Stop the throbber
     */
    var throbber_off = function() {
        $msg.removeClass('updating');
    };

    // return only public methods/properties
    return pub;
})();

jQuery(function() {
    plugin_searchindex.init();
});