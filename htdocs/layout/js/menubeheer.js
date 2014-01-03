/**
 * menubeheer.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery & dragobject.js
 */
$(document).ready(function() {
	menubeheer_knop_init();
});
function menubeheer_knop_init() {
	$('a.confirm').each(function() {
		$(this).click(menubeheer_confirm);
	});
}
function menubeheer_confirm(event) {
	if (!confirm($(this).attr('title') +'.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
}
function menubeheer_clone(id) {
	var clone = $('#inline-newchild-'+ id).clone();
	clone.attr('id', '');
	clone.attr('parentid', id);
	clone.prependTo($('#children-'+ id));
	clone.slideDown();
}