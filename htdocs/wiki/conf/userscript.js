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
