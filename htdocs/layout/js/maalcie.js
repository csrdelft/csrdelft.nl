/**
 * maalcie.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery & dragobject.js
 */

$(document).ready(function () {
	$('#beheer-maalcie-menu').prependTo('#zijkolom').show();
	$('#zijkolom.scroll-fix').parent().height($('#zijkolom').height()); // werkomheen voor vloeiend scrollen
	$('a.ruilen').each(function () {
		$(this).removeClass('ruilen');
		$(this).attr('ondragover', 'taken_mag_ruilen(event);');
		$(this).attr('ondrop', 'taken_ruilen(event);');
	});
});

function taken_toggle_datum(datum) {
	taken_toggle_datum_first(datum, 0);
	$('.taak-datum-' + datum).toggle();
	taken_toggle_datum_first(datum, 1);
	taken_color_datum();

}
function taken_toggle_datum_first(datum, index) {
	if ('taak-datum-head-' + datum === $('#maalcie-tabel tr:visible').eq(index).attr('id')) {
		$('#taak-datum-head-first').toggle();
	}
}
function taken_color_datum() {
	$('tr.taak-datum-summary:visible:odd th').css('background-color', '#FAFAFA');
	$('tr.taak-datum-summary:visible:even th').css('background-color', '#f5f5f5');
}
function taken_show_old() {
	$('#taak-datum-head-first').show();
	$('tr.taak-datum-oud').show();
	taken_color_datum();
}

function taken_toggle_suggestie(soort, show) {
	$('#suggesties-tabel .' + soort).each(function () {
		var verborgen = 0;
		if (typeof show !== 'undefined') {
			if (show) {
				$(this).removeClass(soort + 'verborgen');
			}
			else {
				$(this).addClass(soort + 'verborgen');
			}
		}
		else {
			$(this).toggleClass(soort + 'verborgen');
		}
		if ($(this).hasClass('geenvoorkeurverborgen')) {
			verborgen++;
		}
		if ($(this).hasClass('recentverborgen')) {
			verborgen++;
		}
		if ($(this).hasClass('jongsteverborgen')) {
			verborgen++;
		}
		if ($(this).hasClass('oudereverborgen')) {
			verborgen++;
		}
		if (verborgen > 0) {
			$(this).hide();
		}
		else {
			$(this).show();
		}
	});
	taken_color_suggesties();
}
function taken_color_suggesties() {
	$('#suggesties-tabel tr:visible:odd').css('background-color', '#FAFAFA');
	$('#suggesties-tabel tr:visible:even').css('background-color', '#EBEBEB');
}

var lastSelectedId;
function taken_select_range(e) {
	var shift = bShiftPressed;
	var withinRange = false;
	$("#maalcie-tabel tbody tr td a input[name='" + $(e.target).attr('name') + "']:visible").each(function () {
		var thisId = $(this).attr('id');
		if (thisId === lastSelectedId) {
			withinRange = !withinRange;
		}
		if (thisId === e.target.id) {
			withinRange = !withinRange;
			var check = $(this).prop('checked');
			setTimeout(function () { // workaround e.preventDefault()
				$('#' + thisId).prop('checked', check);
			}, 50);
		}
		else if (shift && withinRange) {
			$(this).prop('checked', true);
		}
	});
	lastSelectedId = e.target.id;
}
function taken_submit_range(e) {
	if (e.target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		e.target = $(e.target).parent();
	}
	$(e.target).find('input').prop('checked', true);
	if ($(e.target).hasClass('confirm') && !confirm($(e.target).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}
	$("input[name='" + $(e.target).find('input:first').attr('name') + "']:visible").each(function () {
		if ($(this).prop('checked')) {
			ajax_request('POST', $(this).parent().attr('href'), $(this).parent().attr('post'), $(this).parent(), dom_update, alert);
		}
	});
}

/* Ruilen van CorveeTaak */

function taken_mag_ruilen(e) {
	if (e.target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		e.target = $(e.target).parent();
	}
	var source = $('#' + dragobjectID);
	if ($(source).attr('id') !== $(e.target).attr('id')) {
		e.preventDefault();
	}
}
function taken_ruilen(e) {
	e.preventDefault();
	var elmnt = e.target;
	if (elmnt.tagName.toUpperCase() === 'IMG') { // dropped on image inside of anchor
		elmnt = $(elmnt).parent();
	}
	var source = $('#' + dragobjectID);
	if (!confirm('Toegekende corveepunten worden meegeruild!\n\nDoorgaan met ruilen?')) {
		return;
	}
	var attr = $(source).attr('uid');
	if (typeof attr === 'undefined' || attr === false) {
		attr = '';
	}
	ajax_request('POST', $(elmnt).attr('href'), 'uid=' + attr, elmnt, dom_update, alert);
	attr = $(elmnt).attr('uid');
	if (typeof attr === 'undefined' || attr === false) {
		attr = '';
	}
	ajax_request('POST', $(source).attr('href'), 'uid=' + attr, source, dom_update, alert);
}
