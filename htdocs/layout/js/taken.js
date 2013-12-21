/**
 * taken.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery & dragobject.js
 */

$(document).ready(function() {
	$('#beheer-taken-menu').prependTo('#mainleft');
	$('#beheer-taken-menu').show();
	taken_form_init();
	taken_link_init();
});

function isShiftKeyDown(event) {
	if ((window.event && window.event.shiftKey) || event.shiftKey) {
		return true;
	}
	return false;
}
function isCtrlKeyDown(event) {
	if ((window.event && window.event.ctrlKey) || event.ctrlKey) {
		return true;
	}
	return false;
}

function taken_form_init() {
	$('.Formulier').each(function() {
		$(this).submit(taken_post_form); // enter
		
		if ($(this).hasClass('popup')) {
			$(this).keyup(function(e) {
				if (e.keyCode === 27) { // esc
					if (confirm('Sluiten zonder op te slaan?')) {
						taken_close_popup();
					}
				}
			});
		}
		else if ($(this).hasClass('taken-hidden-form')) {
			$(this).keyup(function(e) {
				if (e.keyCode === 27) { // esc
					taken_toggle_hiddenform($(this));
				}
			});
		}
	});
}

function taken_link_init() {
	$('a.knop').each(function() {
		if ($(this).hasClass('disabled')) {
			$(this).click(taken_knop_disabled);
		}
		else if ($(this).hasClass('post')) {
			$(this).removeClass('post');
			$(this).click(taken_knop_post);
		}
		else if ($(this).hasClass('get')) {
			$(this).removeClass('get');
			$(this).click(taken_knop_get);
		}
		if ($(this).hasClass('ruilen')) {
			$(this).removeClass('ruilen');
			$(this).attr('ondragover', 'taken_mag_ruilen(event);');
			$(this).attr('ondrop', 'taken_ruilen(event);');
		}
	});
}

function taken_knop_disabled(event) {
	event.preventDefault();
	return false;
}

function taken_knop_get(event) {
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') +'.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
	if ($(this).hasClass('popup')) {
		taken_loading();
	}
	return true;
}

function taken_knop_post(event) {
	event.preventDefault();
	if ($(this).hasClass('range') && event.target.tagName.toUpperCase() === 'INPUT') {
		taken_select_range(event);
		return false;
	}
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') +'.\n\nWeet u het zeker?')) {
		return false;
	}
	var source = $(this);
	if ($(this).hasClass('popup')) {
		taken_loading();
		source = null;
	}
	taken_ajax(source, $(this).attr('href'), taken_handle_response, $(this).attr('post'));
	return false;
}

function taken_post_form(event) {
	event.preventDefault();
	taken_submit_form($(this), false);
	return false;
}

function taken_submit_dropdown(form) {
	if ($(form).hasClass('popup')) {
		taken_loading();
	}
	taken_ajax(null, $(form).attr('action'), taken_handle_response, $(form).serialize());
	taken_reset_form(form);
}

function taken_reset_form(form) {
	$(form).find('.regular').each(function() {
		if ($(this).val() !== $(this).attr('origvalue')) {
			$(this).val($(this).attr('origvalue'));
		}
	});
}

function taken_check_form(form) {
	var changed = false;
	$(form).find('.regular').each(function() {
		if  ($(this).is('input:radio')) {
			if ($(this).is(':checked') && $(this).attr('origvalue') !== $(this).val()) {
				changed = true;
				return false;
			}
		}
		else if ($(this).is('input:checkbox')) {
			if ($(this).is(':checked') !== ($(this).attr('origvalue') === '1')) {
				changed = true;
				return false;
			}
		}
		else if ($(this).val() !== $(this).attr('origvalue')) {
			changed = true;
			return false;
		}
	});
	return changed;
}

function taken_submit_form(form, unchecked, url) {
	if (!unchecked && !taken_check_form(form)) {
		alert('Geen wijzigingen');
		return false;
	}
	var source = form;
	if ($(form).hasClass('popup')) {
		taken_loading();
		$('#taken-popup').remove();
		source = null;
	}
	if (typeof url === 'undefined' || url === false) {
		url = $(form).attr('action');
	}
	taken_ajax(source, url, taken_handle_response, $(form).serialize());
}

function taken_ajax(source, url, successCallback, data) {
	if (typeof source !== 'undefined' && source !== false) {
		$(source).parent().html('<img title="'+ url +'" src="http://plaetjes.csrdelft.nl/layout/loading-arrows.gif" />');
	}
	$.ajax({
		type: 'POST',
		cache: false,
		url: url,
		data: data,
		success: function(response) {
			successCallback(response);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			if (errorThrown === '') {
				errorThrown = 'Nog bezig met laden!';
			}
			$('img[title="'+ this.url +'"]').each(function() {
				this.src = 'http://plaetjes.csrdelft.nl/famfamfam/cancel.png';
				this.title = errorThrown;
			});
			$('#taken-melding').html('<td><div id="melding"><div class="msgerror">'+ errorThrown +'</div></div></td>');
			taken_close_popup();
		}
	});
}

function taken_handle_response(htmlString) {
	$('#taken-melding').html('<td id="taken-melding-veld"></td>');
	htmlString = $.trim(htmlString);
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error'); //DEBUG
		document.write(htmlString);
	}
	else if (htmlString.length > 0) {
		taken_update_dom(htmlString);
	}
	else {
		taken_close_popup();
	}
}

function page_reload() {
	location.reload();
}

function taken_loading() {
	$('#taken-popup-background').css('background-image', 'url("http://plaetjes.csrdelft.nl/layout/loading_bar_black.gif")');
	$('#taken-popup-background').fadeIn();
}

function taken_close_popup() {
	$('#taken-popup').remove();
	$('#taken-popup-background').fadeOut();
}

function taken_toggle_datum(datum) {
	$('.taak-datum-'+ datum).toggle();
	$('#taak-datum-head-first').toggle($('#taak-datum-summery-'+ datum).is(':visible'));
}

function taken_toggle_hiddenform(source) {
	var parent = $(source).parent();
	$(parent).find('div').toggle();
	var form = $(parent).find('form');
	$(form).toggle();
	taken_reset_form(form);
	var elmnt = $(form).find('input[type=text]');
	if ($(elmnt).is(':visible')) {
		var val = $(elmnt).val();
		$(elmnt).focus();
		$(elmnt).val('');
		$(elmnt).val(val); // set focus to end of input
	}
}

function taken_toggle_suggestie(soort, show) {
	$('#suggesties-tabel .'+soort).each(function() {
		var verborgen = 0;
		if (typeof show !== 'undefined') {
			if (show) {
				$(this).removeClass(soort+'verborgen');
			}
			else {
				$(this).addClass(soort+'verborgen');
			}
		}
		else {
			$(this).toggleClass(soort+'verborgen');
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
	$('#suggesties-tabel tr:visible:odd').css('background-color', '#FAFAFA');
	$('#suggesties-tabel tr:visible:even').css('background-color', '#EBEBEB');
}

function taken_update_dom(htmlString) {
	var popup = false;
	var html = $.parseHTML(htmlString);
	$(html).each(function() {
		var id = $(this).attr('id');
		if (id === 'taken-popup') {
			popup = true;
		}
		var ding = $('#' + id);
		if (ding.length === 1) {
			if ($(this).hasClass('remove')) {
				ding.remove();
			}
			else {
				ding.replaceWith($(this));
			}
		}
		else if (popup) {
			taken_loading();
			$('#taken-popup-background').css('background-image', 'none');
			$('#taken-popup-background').after(htmlString);
		}
		else {
			$(this).prependTo('#taken-tabel tbody:first');
		}
	});
	taken_form_init();
	taken_link_init();
	if (popup) {
		$('#taken-popup input:visible:first').focus();
	}
	else {
		taken_close_popup();
	}
}

var lastSelectedId;
function taken_select_range(e) {
	var shift = isShiftKeyDown(e);
	var withinRange = false;
	$("#taken-tabel tbody tr td a input[name='"+$(e.target).attr('name')+"']:visible").each(function() {
		var thisId = $(this).attr('id');
		if (thisId === lastSelectedId) {
			withinRange = !withinRange;
		}
		if (thisId === e.target.id) {
			withinRange = !withinRange;
			var check = $(this).prop('checked');
			setTimeout(function() { // workaround e.preventDefault()
				$('#'+thisId).prop('checked', check);
			}, 50);
		}
		else if (shift && withinRange) {
			$(this).prop('checked', true);
		}
	});
	lastSelectedId = e.target.id;
}
function taken_submit_range(e) {
	if ($(e.target).hasClass('confirm') && !confirm($(e.target).attr('title') +'.\n\nWeet u het zeker?')) {
		return false;
	}
	$("#taken-tabel tbody tr td a input[name='"+$(e.target).attr('name')+"']:visible").each(function() {
		if ($(this).prop('checked')) {
			taken_ajax($(this).parent(), $(this).parent().attr('href'), taken_handle_response, $(this).parent().attr('post'));
		}
	});
}

/**
 * Ruilen van CorveeTaak
 * 
 */
function taken_mag_ruilen(e) {
	if (e.target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		e.target = $(e.target).parent();
	}
	var source = $('#'+dragobjectID);
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
	var source = $('#'+dragobjectID);
	if (!confirm('Toegekende corveepunten worden meegeruild!\n\nDoorgaan met ruilen?')) {
		return;
	}
	var attr = $(source).attr('lid_id');
	if (typeof attr === 'undefined' || attr === false) {
		attr = '';
	}
	taken_ajax(elmnt, $(elmnt).attr('href'), taken_handle_response, 'lid_id='+attr);
	attr = $(elmnt).attr('lid_id');
	if (typeof attr === 'undefined' || attr === false) {
		attr = '';
	}
	taken_ajax(source, $(source).attr('href'), taken_handle_response, 'lid_id='+attr);
}
