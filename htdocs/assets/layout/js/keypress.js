
var bShiftPressed = false;
var bCtrlPressed = false;
var bAltPressed = false;
var bMetaPressed = false;

/**
 * Houdt bij welke modifier keys worden ingedrukt.
 * Werkt dankzij jQuery voor Mac & PC hetzelfde.
 * 
 * Slimme keydown constructie vanwege repeated
 * calls op die functie zolang er een toets wordt
 * ingedrukt.
 */
function init_key_pressed() {
	$(window).on('keyup', function (event) {
		$(window).one('keydown', function (event) {
			key_pressed_update(event);
		});
		key_pressed_update(event);
	});
	$(window).trigger('keyup');
}

function key_pressed_update(event) {
	bShiftPressed = (event.shiftKey ? true : false);
	bCtrlPressed = (event.ctrlKey ? true : false);
	bAltPressed = (event.altKey ? true : false);
	bMetaPressed = (event.metaKey ? true : false);
}

jQuery(document).ready(function () {
	init_key_pressed();
});