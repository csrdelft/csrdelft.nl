/**
 * csrdelft.nl javascript libje...
 */

/**
 * Ajax object
 * @deprecated use jQuery.ajax() instead
 * @type XMLHttpRequest
 */
var http = new XMLHttpRequest();

function preload(arrayOfImages) {
	$(arrayOfImages).each(function () {
		$('<img/>')[0].src = this;
	});
}

preload([
	'http://plaetjes.csrdelft.nl/layout/loading-fb.gif',
	'http://plaetjes.csrdelft.nl/layout/loading-arrows.gif',
	'http://plaetjes.csrdelft.nl/layout/loading_bar_black.gif'
]);

$(document).ready(function () {
	//undo_inline_css();
	zijbalk_scroll_fixed();
	init_dropzone();
	init_sluit_meldingen();
	init();
});

function init() {
	init_buttons();
	init_forms();
	init_timeago();
	init_hoverIntents();
	init_lazy_images();
}

function undo_inline_css() {
	$('#mainright *').removeAttr('style width border cellSpacing cellPadding');
}

function init_dropzone() {
	try {
		Dropzone.autoDiscover = false;
	}
	catch (err) {
		// Missing js file
	}
}

function init_timeago() {
	try {
		$.timeago.settings.strings = {
			prefiprefixAgo: "",
			prefixFromNow: "sinds",
			suffixAgo: "geleden",
			suffixFromNow: "",
			seconds: "minder dan een minuut",
			minute: "1 minuut",
			minutes: "%d minuten",
			hour: "1 uur",
			hours: "%d uur",
			day: "een dag",
			days: "%d dagen",
			month: "een maand",
			months: "%d maanden",
			year: "een jaar",
			years: "%d jaar",
			wordSeparator: " ",
			numbers: []
		};
		$('abbr.timeago').timeago();
	}
	catch (err) {
		// Missing js file
	}
}

function init_lazy_images() {
	$('div.bb-img-loading').each(function () {
		var content = $(document.createElement('IMG'));
		content.error(function () {
			$(this).attr('title', 'Afbeelding bestaat niet of is niet toegankelijk!');
			$(this).attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/picture_error.png');
			$(this).css('width', '16px');
			$(this).css('height', '16px');
			$(this).removeClass('bb-img-loading').addClass('bb-img');
		});
		content.addClass('bb-img');
		content.attr('alt', $(this).attr('title'));
		content.attr('style', $(this).attr('style'));
		content.attr('src', $(this).attr('src'));
		$(this).html(content);
		content.on('load', function () {
			$(this).parent().replaceWith($(this));
		});
	});
}

function init_sluit_meldingen() {
	$('#mainright').on('click', '#melding', function () {
		$(this).fadeOut();
	});
}

function zijbalk_scroll_fixed() {
	var elmnt = $('#zijbalk');
	if (!elmnt.length || !elmnt.hasClass('scroll-fixed')) {
		return;
	}

	if (elmnt.hasClass('desktop-only') && (window.innerWidth < 900 || window.innerHeight < 900)) {
		elmnt.removeClass('desktop-only scroll-fixed dragobject dragvertical');
		return;
	}

	// adjust to container size
	$(window).resize(function () {
		elmnt.css('height', window.innerHeight);
	});
	$(window).trigger('resize');

	// fix position on screen
	$(window).scroll(function () {
		elmnt.css({
			'margin-top': $(window).scrollTop()
		});
	});

	// set scroll position
	elmnt.scrollTop(elmnt.attr('data-scrollfix'));

	// remember scroll position
	var trigger = false;
	var saveCoords = function () {
		$.post('/tools/dragobject.php', {
			id: 'zijbalk',
			coords: {
				top: elmnt.scrollTop(),
				left: elmnt.scrollLeft()
			}
		});
		trigger = false;
	};
	elmnt.scroll(function () {
		if (!trigger) {
			trigger = true;
			$(window).one('mouseup', saveCoords);
		}
	});

	// show-hide scrollbar
	var showscroll = function () {
		if (elmnt.get(0).scrollHeight > elmnt.get(0).clientHeight) {
			elmnt.css({
				'overflow-y': 'scroll'
			});
		}
	};
	var hidescroll = function () {
		elmnt.css({
			'overflow-y': ''
		});
	};
	elmnt.hover(showscroll, hidescroll);
}

function page_reload(htmlString) {
	// prevent hidden errors
	if (typeof htmlString == 'string' && htmlString.substring(0, 24) == '<div id="modal-content">') {
		modal_open(htmlString);
		return;
	}
	location.reload();
}

function init_buttons() {
	$('button.spoiler').unbind('click.spoiler');
	$('button.spoiler').bind('click.spoiler', function (event) {
		event.preventDefault();
		var button = $(this);
		var content = button.next('div.spoiler-content');
		if (button.html() === 'Toon verklapper') {
			button.html('Verberg verklapper');
		}
		else {
			button.html('Toon verklapper');
		}
		content.toggle(800, 'easeInOutCubic');
	});
	$('button.modal').unbind('click.modal');
	$('button.modal').bind('click.modal', function (event) {
		modal_open();
	});
	$('button.post').unbind('click.post');
	$('button.post').bind('click.post', knop_post);
	$('button.get').unbind('click.get');
	$('button.get').bind('click.get', knop_get);
	$('a.opmaakhulp').unbind('click.opmaakhulp');
	$('a.opmaakhulp').bind('click.opmaakhulp', function (event) {
		$('#bbcodehulp').toggle();
	});
	$('a.vergroot').unbind('click.vergroot');
	$('a.vergroot').bind('click.vergroot', function (event) {
		var id = $(this).attr('data-vergroot');
		var height = $(id).height();
		$(id).animate({
			'height': '+=' + height
		}, 600);
	});
	$('a.modal').unbind('click.modal');
	$('a.modal').bind('click.modal', modal_open);
	$('a.post').unbind('click.post');
	$('a.post').bind('click.post', knop_post);
	$('a.get').unbind('click.get');
	$('a.get').bind('click.get', knop_get);
}

function init_hoverIntents() {
	$('.hoverIntent').hoverIntent({
		over: function () {
			$(this).find('.hoverIntentContent').fadeIn();
		},
		out: function () {
			$(this).find('.hoverIntentContent').fadeOut();
		},
		timeout: 250
	});
}

function knop_ajax(knop, type) {
	if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
		modal_close();
		return false;
	}
	var source = knop;
	var done = dom_update;
	var data = knop.attr('data');
	if (knop.hasClass('prompt')) {
		data = data.split('=');
		var val = prompt(data[0], data[1]);
		if (!val) {
			return false;
		}
		data = encodeURIComponent(data[0]) + '=' + encodeURIComponent(val);
	}
	if (knop.hasClass('addfav')) {
		var data = {
			'tekst': document.title.replace('C.S.R. Delft - ', ''),
			'link': this.location.href.replace('http://csrdelft.nl', '')
		};
	}
	if (knop.hasClass('modal')) {
		source = false;
		done = modal_open;
	}
	if (knop.hasClass('ReloadPage')) {
		done = page_reload;
	}
	ajax_request(type, knop.attr('href'), data, source, done, alert);
}

function knop_post(event) {
	event.preventDefault();
	if ($(this).hasClass('range')) {
		if (event.target.tagName.toUpperCase() === 'INPUT') {
			taken_select_range(event);
		}
		else {
			taken_submit_range(event);
		}
		return false;
	}
	knop_ajax($(this), 'POST');
	return false;
}

function knop_get(event) {
	event.preventDefault();
	knop_ajax($(this), 'GET');
	return false;
}

function modal_open(htmlString) {
	if (htmlString) {
		$('#modal').html(htmlString);
		init();
		$('#modal').show();
		$('#modal-background').css('background-image', 'none');
		$('#modal input:visible:first').focus();
	}
	else {
		$('#modal-background').css('background-image', 'url("http://plaetjes.csrdelft.nl/layout/loading_bar_black.gif")');
		$('#modal').hide();
		$('#modal').html('');
	}
	$('#modal-background').fadeIn();
}

function modal_close() {
	$('#modal').hide();
	$('#modal').html('');
	$('#modal-background').fadeOut();
}

function init_forms() {
	$('.Formulier').each(function () {
		var formId = $(this).attr('id');
		if (formId) {
			var init = 'form_ready_' + formId;
			init = init.split('-').join('_');
			if (typeof window[init] === 'function') {
				window[init]();
			}
		}
		$(this).unbind('submit.enter');
		$(this).bind('submit.enter', form_submit);
		$(this).unbind('keyup.esc');
		$(this).bind('keyup.esc', form_esc);
	});
	$('.submit').unbind('click.submit');
	$('.submit').bind('click.submit', form_submit);
	$('.reset').unbind('click.reset');
	$('.reset').bind('click.reset', form_reset);
	$('.cancel').unbind('click.cancel');
	$('.cancel').bind('click.cancel', form_cancel);
	$('.InlineFormToggle').unbind('click.toggle');
	$('.InlineFormToggle').bind('click.toggle', form_toggle);
	$('.SubmitChange').unbind('change.change');
	$('.SubmitChange').bind('change.change', form_submit);
}

function form_ischanged(form) {
	var changed = false;
	$(form).find('.FormElement').each(function () {
		if ($(this).is('input:radio')) {
			if ($(this).is(':checked') && $(this).attr('origvalue') !== $(this).val()) {
				changed = true;
				return false; // break each
			}
		}
		else if ($(this).is('input:checkbox')) {
			if ($(this).is(':checked') !== ($(this).attr('origvalue') === '1')) {
				changed = true;
				return false; // break each
			}
		}
		else if ($(this).val() !== $(this).attr('origvalue')) {
			changed = true;
			return false; // break each
		}
	});
	return changed;
}

function form_set_action(event) {
	var form = $(event.target).closest('form');
	var url = $(event.target).closest('a.knop').attr('href');
	form.attr('action', url);
}

function toggle_vertical_align(elmnt) {
	if ($(elmnt).css('vertical-align') !== 'top') {
		$(elmnt).css('vertical-align', 'top');
	}
	else {
		$(elmnt).css('vertical-align', 'bottom');
	}
}

function toggle_inline_none(elmnt) {
	$(elmnt).css('display', $(elmnt).css('display') === 'none' ? 'inline' : 'none');
}

function form_inline_toggle(form) {
	$(form).find('.InputField').each(function () {
		toggle_inline_none($(this));
	});
	$(form).find('.FormKnoppen').each(function () {
		toggle_inline_none($(this));
	});
	$(form).find('.InlineFormToggle').toggle();
	$(form).find('.FormElement').focus();
}

function form_toggle(event) {
	var form = $(this).closest('form');
	event.preventDefault();
	form_inline_toggle(form);
	return false;
}

function form_esc(event) {
	if (event.keyCode === 27) {
		form_cancel(event);
	}
}

function form_submit(event) {
	var form = $(this).closest('form');
	if (form.hasClass('PreventUnchanged') && !form_ischanged(form)) {
		event.preventDefault();
		alert('Geen wijzigingen');
		return false;
	}
	if (form.hasClass('modal') || form.hasClass('InlineForm')) {
		event.preventDefault();
		var source = false;
		if (form.hasClass('InlineForm')) {
			source = form;
		}
		var done = dom_update;
		if (form.hasClass('ReloadPage')) {
			done = page_reload;
		}
		var formData = new FormData(form.get(0));
		ajax_request('POST', form.attr('action'), formData, source, done, alert, function () {
			if (form.hasClass('SubmitReset')) {
				form_reset(event, form);
			}
		});
		return false;
	}
	form.unbind('submit');
	form.submit();
	return true;
}

function form_reset(event, form) {
	if (!form) {
		form = $(this).closest('form');
		event.preventDefault();
	}
	form.find('.FormElement').each(function () {
		var orig = $(this).attr('origvalue');
		if (typeof orig == 'string') {
			$(this).val(orig);
		}
	});
	return false;
}

function form_cancel(event) {
	var form = $(this).closest('form');
	if (form.hasClass('InlineForm')) {
		event.preventDefault();
		form_inline_toggle(form);
		return false;
	}
	if ($(this).hasClass('post')) {
		event.preventDefault();
		knop_post(event);
		return false;
	}
	if (form.hasClass('modal')) {
		event.preventDefault();
		if (!form_ischanged(form) || confirm('Sluiten zonder wijzigingen op te slaan?')) {
			modal_close();
		}
		return false;
	}
	return true;
}

function dom_update(htmlString) {
	htmlString = $.trim(htmlString);
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error');
		document.write(htmlString);
	}
	var html = $.parseHTML(htmlString);
	$(html).each(function () {
		var id = $(this).attr('id');
		if (id === 'modal-content') {
			modal_open(htmlString);
		}
		else {
			modal_close();
		}
		var elmnt = $('#' + id);
		if (elmnt.length === 1) {
			if ($(this).hasClass('remove')) {
				elmnt.effect('puff', {}, 400, remove);
			}
			else {
				elmnt.replaceWith($(this)).effect('highlight');
			}
		}
		else {
			var parentid = $(this).attr('parentid');
			if (parentid) {
				$(this).prependTo('#' + parentid).effect('highlight');
			}
			else {
				$(this).prependTo('#maalcie-tabel tbody:visible:first').effect('highlight'); //FIXME: make generic
			}
		}
		init();
	});
}

function remove() {
	$(this).remove();
}

function ajax_request(type, url, data, source, onsuccess, onerror, onfinish) {
	if (source) {
		$(source).replaceWith('<img title="' + url + '" src="http://plaetjes.csrdelft.nl/layout/loading-arrows.gif" />');
		source = 'img[title="' + url + '"]';
	}
	else {
		modal_open();
	}
	var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
	var processData = true;
	if (data instanceof FormData) {
		contentType = false;
		processData = false;
	}
	var jqXHR = $.ajax({
		type: type,
		dataType: false,
		contentType: contentType,
		processData: processData,
		url: url,
		cache: false,
		data: data
	});
	jqXHR.done(function (data, textStatus, jqXHR) {
		onsuccess(data);
	});
	jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
		if (errorThrown === '') {
			errorThrown = 'Nog bezig met laden!';
		}
		if (source) {
			$(source).replaceWith('<img title="' + errorThrown + '" src="http://plaetjes.csrdelft.nl/famfamfam/cancel.png" />');
		}
		else {
			modal_close();
		}
		if (onerror) {
			onerror(errorThrown);
		}
	});
	jqXHR.always(function () {
		if (onfinish) {
			onfinish();
		}
	});
}

function ketzer_ajax(url, ketzer) {
	$(ketzer + ' .aanmelddata').html('Aangemeld:<br /><img src="http://plaetjes.csrdelft.nl/layout/loading-arrows.gif" />');
	var jqXHR = $.ajax({
		type: 'GET',
		cache: false,
		url: url,
		data: ''
	});
	jqXHR.done(function (data, textStatus, jqXHR) {
		var html = $.parseHTML(data);
		$('.bb-maaltijd').each(function () {
			if ($(this).attr('id') === $(html).attr('id')) {
				$(this).replaceWith(data);
			}
		});
	});
	jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
		$(ketzer + ' .aanmelddata').html('<span class="error">Error: </span>' + errorThrown);
		alert(errorThrown);
	});
	return true;
}

function peiling_bevestig_stem(peiling) {
	var id = $('input[name=optie]:checked', peiling).val();
	var waarde = $('#label' + id).text();
	if (confirm('Bevestig uw stem:\n\n' + waarde + '\n\n')) {
		$(peiling).submit();
	}
}

function getSelectedText() {
	var text = '';
	if (window.getSelection) {
		text = window.getSelection().toString();
	} else if (document.selection && document.selection.type != 'Control') {
		text = document.selection.createRange().text;
	}
	return text;
}

/**
 * Selecteer de tekst van een DOM-element.
 * @source http://stackoverflow.com/questions/985272/jquery-selecting-text-in-an-element-akin-to-highlighting-with-your-mouse/987376#987376
 * 
 * @param id DOM-object
 */
function selectText(id) {
	var doc = document;
	var text = doc.getElementById(id);
	var range;
	var selection;
	if (doc.body.createTextRange) { //ms
		range = doc.body.createTextRange();
		range.moveToElementText(text);
		range.select();
	} else if (window.getSelection) { //all others
		selection = window.getSelection();
		range = doc.createRange();
		range.selectNodeContents(text);
		selection.removeAllRanges();
		selection.addRange(range);
	}
}

/**
 * Bereken de breedte van een native scrollbalk.
 * @source http://www.alexandre-gomes.com/?p=115
 * 
 * @returns int
 */
function getScrollBarWidth() {
	var inner = document.createElement('p');
	inner.style.width = "100%";
	inner.style.height = "200px";

	var outer = document.createElement('div');
	outer.style.position = "absolute";
	outer.style.top = "0px";
	outer.style.left = "0px";
	outer.style.visibility = "hidden";
	outer.style.width = "200px";
	outer.style.height = "150px";
	outer.style.overflow = "hidden";
	outer.appendChild(inner);

	document.body.appendChild(outer);
	var w1 = inner.offsetWidth;
	outer.style.overflow = 'scroll';
	var w2 = inner.offsetWidth;
	if (w1 === w2) {
		w2 = outer.clientWidth;
	}
	document.body.removeChild(outer);

	return (w1 - w2);
}

function parseBBCode(string, div) {
	var jqXHR = $.ajax({
		type: 'POST',
		cache: false,
		url: '/tools/bbcode.php',
		data: 'data=' + encodeURIComponent(string)
	});
	jqXHR.done(function (data, textStatus, jqXHR) {
		$(div).html(data);
		init();
	});
}

function youtubeDisplay(ytID) {
	$('#youtube' + ytID).html('<object width="640" height="480">' +
			'<param name="movie" value="http://www.youtube.com/v/' + ytID + '&autoplay=1&fs=1"></param><param name="allowFullScreen" value="true"></param>' +
			'<embed src="http://www.youtube.com/v/' + ytID + '&autoplay=1&fs=1" type="application/x-shockwave-flash" wmode="transparent" width="640" height="480" allowfullscreen="true"></embed></object>');
	return false;
}

function readableFileSize(size) {
	var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	var i = 0;
	while (size >= 1024) {
		size /= 1024;
		++i;
	}
	size = size / 1;
	return size.toFixed(1) + ' ' + units[i];
}

function importAgenda(id) {
	var jqXHR = $.ajax({
		type: 'POST',
		cache: false,
		url: '/agenda/courant/',
		data: ''
	});
	jqXHR.done(function (data, textStatus, jqXHR) {
		document.getElementById(id).value += "\n" + data;
	});
}

function CsrBBPreview(source, dest) {
	var code = document.getElementById(source).value;
	if (code.length !== '') {
		var previewDiv = document.getElementById(dest);
		parseBBCode(code, previewDiv);
		$(previewDiv).addClass('preview-show');
		try {
			$(window).scrollTo('#' + source, 1, {
				offset: {
					top: 0,
					left: 0
				}
			});
		} catch (e) {
			// missing scrollTo
		}
	}
}
