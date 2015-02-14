/**
 * csrdelft.nl javascript libje...
 */

/**
 * Ajax object
 * @deprecated use jQuery.ajax() instead
 * @type XMLHttpRequest
 */
var http = new XMLHttpRequest();

function preloadImg(href) {
	$('<img/>')[0].src = href;
}

preloadImg('/plaetjes/layout/loading-fb.gif');
preloadImg('/plaetjes/layout/loading-arrows.gif');
preloadImg('/plaetjes/layout/loading_bar_black.gif');

$(document).ready(function () {
	init_page();
});

function init_page() {
	//undo_inline_css();
	zijbalk_scroll_fixed();
	init_dropzone();
	init_timeago_once();
	init_tooltip_once();
	init_sluit_meldingen();
	init_context($('body'));
	init_geolocation();
}

function init_context(parent) {
	init_buttons(parent);
	init_forms(parent);
	init_timeago(parent);
	init_markitup(parent);
	init_tooltips(parent);
	init_hoverIntents(parent);
	init_lazy_images(parent);
}

function undo_inline_css() {
	$('#mainright *').removeAttr('style width border cellSpacing cellPadding');
}

function init_dropzone() {
	try {
		Dropzone.autoDiscover = false;
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}
}

function init_timeago_once() {
	try {
		$.timeago.settings.strings = {
			prefiprefixAgo: "",
			prefixFromNow: "sinds",
			suffixAgo: "geleden",
			suffixFromNow: "",
			seconds: "nog geen minuut",
			minute: "1 minuut",
			minutes: "%d minuten",
			hour: "1 uur",
			hours: "%d uur",
			day: "1 dag",
			days: "%d dagen",
			month: "1 maand",
			months: "%d maanden",
			year: "1 jaar",
			years: "%d jaar",
			wordSeparator: " ",
			numbers: []
		};
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}
}

function init_timeago(parent) {
	try {
		$(parent).find('abbr.timeago').timeago();
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}
}

function init_tooltip_once() {
	try {
		// Change JQueryUI/tooltip plugin name to 'uitooltip' to fix name collision with Bootstrap/tooltip
		$.widget.bridge('uitooltip', $.ui.tooltip);
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}
}

function init_tooltips(parent) {
	try {
		$(parent).uitooltip({
			track: true
		});
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}
}

function init_markitup(parent) {
	try {
		$(parent).find('textarea.BBCodeField').markItUp(CsrBBcodeMarkItUpSet); // CsrBBcodeMarkItUpSet is located in: /layout/js/markitup/sets/bbcode/set.js
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}
}

function init_geolocation() {

	var prev_pos = false;

	var position_save = function (position) {
		if (!prev_pos || ($(prev_pos.coords).not(position.coords).length === 0 && $(position.coords).not(prev_pos.coords).length === 0)) {
			prev_pos = position;
			$.post('/geolocation/save', {
				coords: position.coords,
				timestamp: position.timestamp
			});
		}
	};

	var position_error = function (error) {
		switch (error.code) {
			case error.PERMISSION_DENIED:
				break;
			case error.POSITION_UNAVAILABLE:
				break;
			case error.TIMEOUT:
				break;
			case error.UNKNOWN_ERROR:
				break;
		}
	};

	if (navigator.geolocation) {
		navigator.geolocation.watchPosition(position_save, position_error);
	}
}

function init_lazy_images(parent) {
	$(parent).find('div.bb-img-loading').each(function () {
		var content = $(document.createElement('IMG'));
		content.error(function () {
			$(this).attr('title', 'Afbeelding bestaat niet of is niet toegankelijk!');
			$(this).attr('src', '/plaetjes/famfamfam/picture_error.png');
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
			var foto = content.attr('src').indexOf('/plaetjes/fotoalbum/') >= 0;
			var video = $(this).parent().parent().hasClass('bb-video-preview');
			$(this).parent().replaceWith($(this));
			if (!foto && !video) {
				$(this).wrap('<a class="lightbox-link" href="' + $(this).attr('src') + '" data-lightbox="page-lightbox"></a>');
			}
		});
	});
}

function init_sluit_meldingen() {
	$('#melding').on('click', '.alert', function () {
		$(this).slideUp(400, remove);
	});
}

function zijbalk_scroll_fixed() {
	var elmnt = $('#cd-zijbalk');
	if (!elmnt.length || !elmnt.hasClass('scroll-fixed')) {
		return;
	}

	if (elmnt.hasClass('desktop-only') && /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		elmnt.removeClass('desktop-only scroll-fixed dragobject dragvertical scroll-hover');
		return;
	}

	// adjust to container size
	$(window).resize(function () {
		elmnt.css('height', document.documentElement.clientHeight);
	});
	$(window).trigger('resize');

	// fix position on screen
	$(window).scroll(function () {
		elmnt.css({
			'top': $(window).scrollTop()
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
	if (elmnt.hasClass('scroll-hover')) {
		var showscroll = function () {
			if (elmnt.get(0).scrollHeight > elmnt.get(0).clientHeight) {
				elmnt.css({
					'overflow-y': 'scroll'
				});
			}
		};
		var hidescroll = function () {
			elmnt.css({
				'overflow-y': 'hidden'
			});
		};
		elmnt.hover(showscroll, hidescroll);
	}
}

function page_reload(htmlString) {
	// prevent hidden errors
	if (typeof htmlString == 'string' && htmlString.substring(0, 16) == '<div id="modal" ') {
		modal_open(htmlString);
		return;
	}
	location.reload();
}

function page_redirect(htmlString) {
	// prevent hidden errors
	if (typeof htmlString == 'string' && htmlString.substring(0, 16) == '<div id="modal" ') {
		modal_open(htmlString);
		return;
	}
	window.location.href = htmlString;
}

function init_hoverIntents(parent) {
	$(parent).find('.hoverIntent').hoverIntent({
		over: function () {
			$(this).find('.hoverIntentContent').fadeIn();
		},
		out: function () {
			$(this).find('.hoverIntentContent').fadeOut();
		},
		timeout: 250
	});
}

function init_buttons(parent) {
	$(parent).find('.spoiler').bind('click.spoiler', function (event) {
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
	$(parent).find('.popup').bind('click.popup', modal_open);
	$(parent).find('.post').bind('click.post', knop_post);
	$(parent).find('.get').bind('click.get', knop_get);
	$(parent).find('.vergroot').bind('click.vergroot', function (event) {
		knop_vergroot($(this));
	});
}

function knop_vergroot(knop) {
	var id = knop.attr('data-vergroot');
	var oud = knop.attr('data-vergroot-oud');
	if (oud) {
		$(id).animate({
			'height': oud
		}, 600);
		knop.removeAttr('data-vergroot-oud');
		knop.find('span.fa').removeClass('fa-compress').addClass('fa-expand');
		knop.attr('title', 'Uitklappen');
	}
	else {
		knop.attr('title', 'Inklappen');
		knop.find('span.fa').removeClass('fa-expand').addClass('fa-compress');
		knop.attr('data-vergroot-oud', $(id).height());
		$(id).animate({
			'height': $(id).prop('scrollHeight') + 1
		}, 600);
	}
}

function knop_ajax(knop, type) {
	if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
		modal_close();
		return false;
	}
	var source = knop;
	var done = dom_update;
	var data = knop.attr('data');

	if (knop.hasClass('popup')) {
		source = false;
	}
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
			'link': this.location.href
		};
	}
	if (knop.hasClass('DataTableResponse')) {

		var tableId = knop.attr('data-tableid');
		if (!document.getElementById(tableId)) {
			tableId = knop.closest('form').attr('data-tableid');
			if (!document.getElementById(tableId)) {
				alert('DataTable not found');
			}
		}

		data = {
			'DataTableId': tableId
		};
		var selection = fnGetSelection('#' + tableId);
		if (selection.length > 0) {
			data = {
				'DataTableId': tableId,
				'DataTableSelection[]': selection
			};
		}

		done = function (response) {
			if (typeof response === 'object') { // JSON
				fnUpdateDataTable(tableId, response);
				if (response.modal) {
					modal_open(response.modal);
					init_context($('#modal'));
				}
				else {
					modal_close();
				}
			}
			else { // HTML
				dom_update(response);
			}
		};

		if (!knop.hasClass('SingleRow')) {
			source = false;
		}
	}
	if (knop.hasClass('ReloadPage')) {
		done = page_reload;
	}
	else if (knop.hasClass('redirect')) {
		done = page_redirect;
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
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
		htmlString.preventDefault();
		return false;
	}
	if (typeof htmlString == 'string' && htmlString != '') {
		$('#modal').html(htmlString);
		$('#modal').show();
		$('#modal-background').css('background-image', 'none');
		$('#modal input:visible:first').focus();
	}
	else {
		$('#modal-background').css('background-image', 'url("/plaetjes/layout/loading_bar_black.gif")');
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

function init_forms(parent) {
	$(parent).find('form').submit(form_submit);
	$(parent).find('.submit').bind('click.submit', form_submit);
	$(parent).find('.reset').bind('click.reset', form_reset);
	$(parent).find('.cancel').bind('click.cancel', form_cancel);
	$(parent).find('.InlineFormToggle').bind('click.toggle', form_toggle);
	$(parent).find('.SubmitChange').bind('change.change', form_submit);
}

function form_ischanged(form) {
	var changed = false;
	$(form).find('.FormElement').not('.tt-hint').each(function () {
		var elmnt = $(this);
		if (elmnt.is('input:radio')) {
			if (elmnt.is(':checked') && elmnt.attr('origvalue') !== elmnt.val()) {
				changed = true;
				return false; // break each
			}
		}
		else if (elmnt.is('input:checkbox')) {
			if (elmnt.is(':checked') !== (elmnt.attr('origvalue') === '1')) {
				changed = true;
				return false; // break each
			}
		}
		else if (elmnt.val() !== elmnt.attr('origvalue')) {
			changed = true;
			return false; // break each
		}
	});
	return changed;
}

function toggle_vertical_align(elmnt) {
	if ($(elmnt).css('vertical-align') !== 'top') {
		$(elmnt).css('vertical-align', 'top');
	}
	else {
		$(elmnt).css('vertical-align', 'bottom');
	}
}

function form_inline_toggle(form) {
	form.prev('.InlineFormToggle').toggle();
	form.toggle();
	form.children(':first').focus();
}

function form_toggle(event) {
	event.preventDefault();
	var form = $(this).next('form');
	form_inline_toggle(form);
	return false;
}

function form_submit(event) {
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}

	var form = $(this).closest('form');
	if (!form.hasClass('Formulier')) {
		if (event) {
			form = $(event.target.form);
		}
		else {
			return false;
		}
	}

	if (form.hasClass('PreventUnchanged') && !form_ischanged(form)) {
		event.preventDefault();
		alert('Geen wijzigingen');
		return false;
	}

	if ($(this).attr('href')) {
		form.attr('action', $(this).attr('href'));
	}

	if (form.hasClass('ModalForm') || form.hasClass('InlineForm')) {
		event.preventDefault();
		var formData = new FormData(form.get(0));
		var done = dom_update;
		var source = false;

		if (form.hasClass('InlineForm')) {
			source = form;
			formData.append('InlineFormId', form.attr('id'));
		}

		if (form.hasClass('DataTableResponse')) {

			var tableId = form.attr('data-tableid');
			if (!document.getElementById(tableId)) {
				alert('DataTable not found');
			}

			formData.append('DataTableId', tableId);
			var selection = fnGetSelection('#' + tableId);
			$.each(selection, function (key, value) {
				formData.append('DataTableSelection[]', value);
			});

			done = function (response) {
				if (typeof response === 'object') { // JSON
					fnUpdateDataTable(tableId, response);
					if (response.modal) {
						modal_open(response.modal);
						init_context($('#modal'));
					}
					else {
						modal_close();
					}
				}
				else { // HTML
					dom_update(response);
				}
			};

			if (!form.hasClass('noanim')) {
				source = false;
			}
		}

		if (form.hasClass('ReloadPage')) {
			done = page_reload;
		}
		else if (form.hasClass('redirect')) {
			done = page_redirect;
		}

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
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
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
	var source = $(event.target);
	if (source.length === 0) {
		source = $(this);
	}
	if (source.hasClass('confirm') && !confirm(source.attr('title') + '.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
	var form = source.closest('form');
	if (form.hasClass('InlineForm')) {
		event.preventDefault();
		form_inline_toggle(form);
		return false;
	}
	if (source.hasClass('post')) {
		event.preventDefault();
		knop_post(event);
		return false;
	}
	if (form.hasClass('ModalForm')) {
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
	var html = $.parseHTML(htmlString, document, true);
	$(html).each(function () {
		var id = $(this).attr('id');
		if (id === 'modal') {
			modal_open();
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
				elmnt.replaceWith($(this).show()).effect('highlight');
			}
		}
		else {
			var parentid = $(this).attr('parentid');
			if (parentid) {
				$(this).prependTo('#' + parentid).show().effect('highlight');
			}
			else {
				$(this).prependTo('#maalcie-tabel tbody:visible:first').show().effect('highlight'); //FIXME: make generic
			}
		}
		init_context($(this));
		if (id === 'modal') {
			$('#modal-background').css('background-image', 'none');
			$('#modal input:visible:first').focus();
		}
	});
}

function remove() {
	$(this).remove();
}

function ajax_request(type, url, data, source, onsuccess, onerror, onfinish) {
	if (source) {
		if (!source.hasClass('noanim')) {
			$(source).replaceWith('<img id="' + source.attr('id') + '" title="' + url + '" src="/plaetjes/layout/loading-arrows.gif" />');
			source = 'img[title="' + url + '"]';
		}
		else if (source.hasClass('InlineForm')) {
			$(source).find('.FormElement:first').css({
				'background-image': 'url("/plaetjes/layout/loading-fb.gif")',
				'background-repeat': 'no-repeat',
				'background-position': 'center right'
			});
		}
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
		contentType: contentType,
		processData: processData,
		url: url,
		cache: false,
		data: data
	});
	jqXHR.done(function (data, textStatus, jqXHR) {
		onsuccess(data);
		if (source && source.hasClass('InlineForm') && source.hasClass('noanim')) {
			$(source).find('.FormElement:first').css({
				'background-image': '',
				'background-repeat': '',
				'background-position': ''
			});
		}
	});
	jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
		if (errorThrown === '') {
			errorThrown = 'Nog bezig met laden!';
		}
		if (source) {
			$(source).replaceWith('<img title="' + errorThrown + '" src="/plaetjes/famfamfam/cancel.png" />');
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
	$(ketzer + ' .aanmelddata').html('Aangemeld:<br /><img src="/plaetjes/layout/loading-arrows.gif" />');
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
 * @param elmnt DOM-object
 */
function selectText(elmnt) {
	var range;
	var selection;
	if (document.body.createTextRange) { //ms
		range = document.body.createTextRange();
		range.moveToElementText(elmnt);
		range.select();
	} else if (window.getSelection) { //all others
		selection = window.getSelection();
		range = document.createRange();
		range.selectNodeContents(elmnt);
		selection.removeAllRanges();
		selection.addRange(range);
	}
}

/**
 * @source http://stackoverflow.com/a/7228322
 * @param {Number} min
 * @param {Number} max
 * @returns {Number}
 */
function randomIntFromInterval(min, max) {
	return Math.floor(Math.random() * (max - min + 1) + min);
}

function dirname(path) {
	//  discuss at: http://phpjs.org/functions/dirname/
	// original by: Ozh
	// improved by: XoraX (http://www.xorax.info)
	//   example 1: dirname('/etc/passwd');
	//   returns 1: '/etc'
	//   example 2: dirname('c:/Temp/x');
	//   returns 2: 'c:/Temp'
	//   example 3: dirname('/dir/test/');
	//   returns 3: '/dir'

	return path.replace(/\\/g, '/')
			.replace(/\/[^\/]*\/?$/, '');
}

function basename(path, suffix) {
	//  discuss at: http://phpjs.org/functions/basename/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// improved by: Ash Searle (http://hexmen.com/blog/)
	// improved by: Lincoln Ramsay
	// improved by: djmix
	// improved by: Dmitry Gorelenkov
	//   example 1: basename('/www/site/home.htm', '.htm');
	//   returns 1: 'home'
	//   example 2: basename('ecra.php?p=1');
	//   returns 2: 'ecra.php?p=1'
	//   example 3: basename('/some/path/');
	//   returns 3: 'path'
	//   example 4: basename('/some/path_ext.ext/','.ext');
	//   returns 4: 'path_ext'

	var b = path;
	var lastChar = b.charAt(b.length - 1);

	if (lastChar === '/' || lastChar === '\\') {
		b = b.slice(0, -1);
	}

	b = b.replace(/^.*[\/\\]/g, '');

	if (typeof suffix === 'string' && b.substr(b.length - suffix.length) == suffix) {
		b = b.substr(0, b.length - suffix.length);
	}

	return b;
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

function CsrBBPreview(sourceId, targetId) {
	if (sourceId.charAt(0) !== '#') {
		sourceId = '#' + sourceId;
	}
	if (targetId.charAt(0) !== '#') {
		targetId = '#' + targetId;
	}
	var bbcode = $(sourceId).val();
	if (typeof bbcode !== 'string' || bbcode.trim() === '') {
		$(targetId).html('').hide();
		return;
	}
	$.post('/tools/bbcode.php', {
		data: encodeURIComponent(bbcode)
	}).done(function (data, textStatus, jqXHR) {
		if (targetId.charAt(0) !== '#') {
			targetId = '#' + targetId;
		}
		$(targetId).html(data);
		init_context($(targetId));
		$(targetId).show();
	}).fail(alert);
}