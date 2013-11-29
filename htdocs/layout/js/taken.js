/**
 * taken.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 */

$(document).ready(function() {
	$('#beheer-taken-menu').prependTo('#mainleft');
	$('#beheer-taken-menu').show();
	taken_form_init();
	taken_link_init();
	taken_popup_init();
});

function taken_form_init() {
	$('.Formulier').each(function() {
		$(this).submit(taken_post_form); // enter
		
		if ($(this).hasClass('taken-hidden-form')) {
			$(this).keyup(function(e) {
				if (e.keyCode === 27) { // esc
					taken_toggle_hiddenform($(this));
				}
			});
		}
	});
}

function taken_popup_init() {
	var p = document.getElementById('taken-popup');
	if (p) {
		p.addEventListener('mousedown', startDrag, false);
		window.addEventListener('mouseup', stopDrag, false);
	}
}

function taken_link_init() {
	$('a.knop').each(function() {
		if ($(this).hasClass('post')) {
			$(this).removeClass('post');
			$(this).click(taken_post_knop);
		}
		else if ($(this).hasClass('get')) {
			$(this).removeClass('get');
			$(this).click(taken_get_knop);
		}
		if ($(this).hasClass('ruilen')) {
			$(this).removeClass('ruilen');
			$(this).attr('draggable', 'true');
			$(this).attr('ondragstart', 'handleDragStart(event);');
			$(this).attr('ondragover', 'handleDragOver(event);');
			$(this).attr('ondrop', 'handleDrop(event);');
		}
	});
}

function taken_get_knop(event) {
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') +'.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
	if ($(this).hasClass('popup')) {
		taken_loading();
	}
	return true;
}

function taken_post_knop(event) {
	event.preventDefault();
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
			if ($(this).is(':checked') && $(this).attr('origvalue') !== '1') {
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
	$('.taak-datum-' + datum).toggle();
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
		taken_popup_init();
	}
	else {
		taken_close_popup();
	}
}


/**
 * Drag popup
 * 
 */
var offsetX = 0;
var offsetY = 0;
function startDrag(e) {
	e = e || window.event;
	if (e.target.id === 'taken-popup') {
		offsetX = mouseX(e);
		offsetY = mouseY(e);
		window.addEventListener('mousemove', mouseMoveHandler, true);
	}
}
function stopDrag(e) {
	window.removeEventListener('mousemove', mouseMoveHandler, true);
}
function mouseMoveHandler(e) {
	e = e || window.event;
	var x = mouseX(e);
	var y = mouseY(e);
	if (x !== offsetX || y !== offsetY) {
		var p = document.getElementById('taken-popup');
		var l = parseInt(p.style.left);
		var t = parseInt(p.style.top);
		if (isNaN(l)) l = $('#taken-popup').offset().left - (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
		if (isNaN(t)) t = $('#taken-popup').offset().top - (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
		p.style.left = (l + x - offsetX) + 'px';
		p.style.top  = (t + y - offsetY) + 'px';
		offsetX = x;
		offsetY = y;
	}
}
function mouseX(e) {
	if (e.pageX) {
	  return e.pageX;
	}
	if (e.clientX) {
		return e.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	}
	return null;
}
function mouseY(e) {
	if (e.pageY) {
		return e.pageY;
	}
	if (e.clientY) {
		return e.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	}
	return null;
}


/**
 * Ruilen van CorveeTaak
 * 
 */
function handleDragStart(e) {
	e.dataTransfer.setData('Text', e.target.id);
}
function handleDragOver(e) {
	var elmnt = e.target;
	if (elmnt.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		elmnt = $(elmnt).parent();
	}
	var source = $('#'+e.dataTransfer.getData('Text'));
	if ($(source).attr('id') !== $(elmnt).attr('id')) {
		e.preventDefault();
	}
}
function handleDrop(e) {
	e.preventDefault();
	var elmnt = e.target;
	if (elmnt.tagName.toUpperCase() === 'IMG') { // dropped on image inside of anchor
		elmnt = $(elmnt).parent();
	}
	var source = $('#'+e.dataTransfer.getData('Text'));
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
