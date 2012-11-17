/**
 * Provides a list of matching user names while user inputs into a userpicker
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
jQuery(function () {
    function prepareLi (li, value) {
            var name = value[0];
            li.innerHTML = '<a href="#">' + value[1] + ' (' + name + ')' + '</a>';
            li.id = 'bureaucracy__user__' + name.replace(/\W/g, '_');
            li._value = name;
    };

    var classes = { 'userpicker': false, 'userspicker': true };
    for (var c_class in classes) {
        jQuery('input.'+c_class).each(function(){
            addAutoCompletion(this, 'bureaucracy_user_field', classes[c_class], prepareLi);
        });
      /*  var pickers = getElementsByClass(c_class, document, 'input');
        for (var i = 0 ; i < pickers.length ; ++i) {
            addAutoCompletion(pickers[i], 'bureaucracy_user_field', classes[c_class], prepareLi);
        }      */
    }
});
