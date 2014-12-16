/**
 * Add extra button to wrap toolbar
 *  - blockquote
 */
// try to add button to toolbar
if (window.toolbar != undefined) {
    jQuery.each(window.toolbar, function(i, button) {
        if(button.title == "Wrap Plugin") {
            button.list[button.list.length] = {
                title: "blockquote",
                icon: "../../plugins/wrap/images/toolbar/blockquote.png",
                open: "<WRAP blockquote>",
                close: "</WRAP>",
                type: "format"
            }
        }
    });
}
jQuery(function(){
    //geef een module icoontje weer ipv de tekst 'Je bent hier:'
    jQuery('span.bchead:first')
        .after('<a href="/wiki/" title="Wiki"><img src="/plaetjes/knopjes/wiki.png" class="module-icon"></a> » ')
        .hide();

    //vervang url in de wikiheader
    jQuery('#dokuwiki__header').
        find('.headings h1 a').attr('href', '/');
});

/**
 * Discussion Plugin
 *
 * initial hide the toolbar, show toolbar on focus
 */
jQuery('#discussion__comment_toolbar').hide();
jQuery('#discussion__comment_text').focus(function () {
    jQuery('#discussion__comment_toolbar').show();
});
