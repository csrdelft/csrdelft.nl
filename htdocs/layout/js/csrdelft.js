/*
 * csrdelft.nl javascript libje...
 */

$(document).ready(function() {
	init_links('');
	init_forms('');
	init_visitekaartjes('');
	ShowMenu(menu_active);
});

function page_reload() {
	location.reload();
}

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
/*
 function htmlDecode(input) {
 var div = document.createElement('div');
 div.innerHTML = input;
 return div.childNodes.length === 0 ? '' : div.childNodes[0].nodeValue;
 }
 */

function init_visitekaartjes(context) {
	$(context + '.visite').hoverIntent(function() {
		var id = $(this).attr('id');
		id = id.replace('v', 'k');
		$('#' + id).fadeIn();
	});
	$(context + '.visitekaartje').mouseleave(function() {
		$(this).fadeOut();
	});
}

function init_links(context) {
	$(context + 'a.post').click(knop_post);
	$(context + 'a.get').click(knop_get);
}

function knop_ajax(knop, type) {
	if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}
	var source = knop;
	var done = dom_update;
	if (knop.hasClass('popup')) {
		source = false;
		done = popup_open;
	}
	ajax_request(type, knop.attr('href'), knop.attr('postdata'), source, done, alert);
}

function knop_post(event) {
	event.preventDefault();
	knop_ajax($(this), 'POST');
	return false;
}

function knop_get(event) {
	event.preventDefault();
	knop_ajax($(this), 'GET');
	return false;
}

function popup_open(htmlString) {
	if (htmlString) {
		$('#popup').html(htmlString);
		init_forms('#popup ');
		init_links('#popup ');
		init_visitekaartjes('#popup ');
		$('#popup').show();
		$('#popup-background').css('background-image', 'none');
		$('#popup input:visible:first').focus();
	}
	else {
		$('#popup-background').css('background-image', 'url("http://plaetjes.csrdelft.nl/layout/loading_bar_black.gif")');
		$('#popup').hide();
		$('#popup').html('');
	}
	$('#popup-background').fadeIn();
}

function popup_close() {
	$('#popup').hide();
	$('#popup').html('');
	$('#popup-background').fadeOut();
}

function init_forms(context) {
	$(context + '.submit').click(form_submit);
	$(context + '.reset').click(form_reset);
	$(context + '.cancel').click(form_cancel);
	$(context + '.SubmitChange').change(form_submit);
	$(context + '.Formulier').each(function() {
		$(this).submit(form_submit); // enter

		$(this).keyup(function(event) {
			if (event.keyCode === 27) { // esc
				form_cancel(event);
			}
		});
	});
}

function form_ischanged(form) {
	var changed = false;
	$(form).find('.FormField').each(function() {
		if ($(this).is('input:radio')) {
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

function form_inline_toggle(form) {
	$(form).find('.FormToggle').toggle();
	$(form).find('.FormField').toggle().focus();
	$(form).find('.knop').toggle();
}

function form_submit(event) {
	var form = $(this).closest('form');
	if (form.hasClass('PreventUnchanged') && !form_ischanged(form)) {
		event.preventDefault();
		alert('Geen wijzigingen');
		return false;
	}
	if (form.hasClass('popup') || form.hasClass('InlineForm')) {
		event.preventDefault();
		var source = false;
		if (form.hasClass('InlineForm')) {
			source = form;
		}
		ajax_request('POST', form.attr('action'), form.serialize(), source, dom_update, alert, function() {
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
	form.find('.FormField').each(function() {
		var orig = $(this).attr('origvalue');
		if (orig) {
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
	if (form.hasClass('popup')) {
		event.preventDefault();
		if (confirm('Sluiten zonder op te slaan?')) {
			popup_close();
		}
		return false;
	}
	return true;
}

function dom_update(htmlString) {
	htmlString = $.trim(htmlString);
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error'); //DEBUG
		document.write(htmlString);
	}
	var html = $.parseHTML(htmlString);
	$(html).each(function() {
		var id = $(this).attr('id');
		if (id === 'popup-content') {
			popup_open(htmlString);
		}
		else {
			popup_close();
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
			$(this).prependTo('#' + $(this).attr('parentid')).effect('highlight');
		}
		init_forms('#' + id + ' ');
		init_links('#' + id + ' ');
		init_visitekaartjes('#' + id + ' ');
	});
}

function remove() {
	$(this).remove();
}

function ajax_request(type, url, data, source, onsuccess, onerror, onfinish) {
	if (source) {
		$(source).replaceWith('<img title="' + url + '" src="http://plaetjes.csrdelft.nl/layout/loading-arrows.gif" />');
	}
	else {
		popup_open();
	}
	var jqXHR = $.ajax({
		type: type,
		cache: false,
		url: url,
		data: data
	});
	jqXHR.done(function(data, textStatus, jqXHR) {
		onsuccess(data);
	});
	jqXHR.fail(function(jqXHR, textStatus, errorThrown) {
		if (errorThrown === '') {
			errorThrown = 'Nog bezig met laden!';
		}
		if (source) {
			$(source).replaceWith('<img title="' + errorThrown + '" src="http://plaetjes.csrdelft.nl/famfamfam/cancel.png" />');
		}
		else {
			popup_close();
		}
		if (onerror) {
			onerror(errorThrown);
		}
	});
	jqXHR.always(function() {
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
	jqXHR.done(function(data, textStatus, jqXHR) {
		var html = $.parseHTML(data);
		$('.ubb_maaltijd').each(function() {
			if ($(this).attr('id') === $(html).attr('id')) {
				$(this).replaceWith(data);
			}
		});
	});
	jqXHR.fail(function(jqXHR, textStatus, errorThrown) {
		$(ketzer + ' .aanmelddata').html('<span style="color: red; font-weight: bold;">Error:</span><br />' + errorThrown);
		alert(errorThrown);
	});
	return true;
}

/**
 * Selecteer de tekst van een DOM-element
 * http://stackoverflow.com/questions/985272/jquery-selecting-text-in-an-element-akin-to-highlighting-with-your-mouse/987376#987376
 * 
 * @param element DOM-object
 */
function selectText(element) {
	var doc = document;
	var text = doc.getElementById(element);
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

//we maken een standaard AJAX-ding aan.
var http = false;
if (navigator.appName == "Microsoft Internet Explorer") {
	http = new ActiveXObject("Microsoft.XMLHTTP");
} else {
	http = new XMLHttpRequest();
}

function vergrootTextarea(id, rows) {
	var textarea = document.getElementById(id);
	//if (!textarea || (typeof(textarea.rows) == "undefined")) return;
	var currentRows = textarea.rows;
	textarea.rows = currentRows + rows;
}
function setjs() {
	if (navigator.product == 'Gecko') {
		document.loginform["interface"].value = 'mozilla';
	} else if (window.opera && document.childNodes) {
		document.loginform["interface"].value = 'opera7';
	} else if (navigator.appName == 'Microsoft Internet Explorer' &&
			navigator.userAgent.indexOf("Mac_PowerPC") > 0) {
		document.loginform["interface"].value = 'konqueror';
	} else if (navigator.appName == 'Microsoft Internet Explorer' &&
			document.getElementById && document.getElementById('ietest').innerHTML) {
		document.loginform["interface"].value = 'ie';
	} else if (navigator.appName == 'Konqueror') {
		document.loginform["interface"].value = 'konqueror';
	} else if (window.opera) {
		document.loginform["interface"].value = 'opera';
	}
}
function nickvalid() {
	var nick = document.loginform.Nickname.value;
	if (nick.match(/^[A-Za-z0-9\[\]\{\}^\\\|\_\-`]{1,32}$/))
		return true;
	alert('Kies een geldige nickname!');
	//document.loginform.Nickname.value = nick.replace(/[^A-Za-z0-9\[\]\{\}^\\\|\_\-`]/g, '');
	return false;
}
function setcharset() {
	if (document.charset && document.loginform["Character set"])
		document.loginform['Character set'].value = document.charset
}
function bevestig(tekst) {
	return confirm(tekst);
}
function previewPost(source, dest) {
	var post = document.getElementById(source).value;
	if (post.length != '') {
		var previewDiv = document.getElementById(dest);
		applyUBB(post, previewDiv);
		$('#' + dest + "Container").show();
	}
}
/*
 * Apply UBB to a string, and put it in innerHTML of given div.
 *
 * Example:
 * applyUBB('[url=http://csrdelft.nl]csrdelft.nl[/url]', document.getElementById('berichtPreview'));
 */
function applyUBB(string, div) {
	http.abort();
	var params = "string=" + encodeURIComponent(string);
	http.open("POST", "/tools/ubb.php", true);
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Connection", "close");

	http.onreadystatechange = function() {
		if (http.readyState == 4) {
			div.innerHTML = http.responseText;
		}
	}
	http.send(params);
}

/*
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 */
var bewerkDiv = null;
var bewerkDivInnerHTML = null;
function forumBewerken(post) {
	http.abort();
	http.open("GET", "/communicatie/forum/getPost.php?post=" + post, true);
	http.onreadystatechange = function() {
		if (http.readyState == 4) {
			if (document.getElementById('forumEditForm')) {
				restorePost();
			}

			bewerkDiv = document.getElementById('post' + post);
			bewerkDivInnerHTML = bewerkDiv.innerHTML;

			bewerkForm = '<form action="/communicatie/forum/bewerken/' + post + '" method="post" id="forumEditForm">';
			bewerkForm += '<h3>Bericht bewerken</h3>Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]<br />';
			bewerkForm += '<div id="bewerkPreviewContainer" class="previewContainer"><h3>Voorbeeld van uw bericht:</h3><div id="bewerkPreview" class="preview"></div></div>';
			bewerkForm += '<textarea name="bericht" id="forumBewerkBericht" class="tekst" rows="8" style="width: 100%;"></textarea>';
			bewerkForm += 'Reden van bewerking: <input type="text" name="reden" style="width: 250px;"/><br /><br />';
			bewerkForm += '<a style="float: right;" class="handje knop" onclick="$(\'#ubbhulpverhaal\').toggle();" title="Opmaakhulp weergeven">UBB</a>';
			bewerkForm += '<a style="float: right;" class="handje knop" onclick="vergrootTextarea(\'forumBewerkBericht\', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>';
			bewerkForm += '<input type="submit" value="opslaan" /> <input type="button" value="voorbeeld" onclick="previewPost(\'forumBewerkBericht\', \'bewerkPreview\')" /> <input type="button" value="terug" onclick="restorePost()" />';
			bewerkForm += '</form>';

			bewerkDiv.innerHTML = bewerkForm;
			document.getElementById('forumBewerkBericht').value = http.responseText;

			//invoerveldjes van het normale toevoegformulier even uitzetten.
			document.getElementById('forumBericht').disabled = true;
			document.getElementById('forumOpslaan').disabled = true;
			document.getElementById('forumVoorbeeld').disabled = true;
		}
	}
	http.send(null);
	return false;
}
function restorePost() {
	bewerkDiv.innerHTML = bewerkDivInnerHTML;
	document.getElementById('forumBericht').disabled = false;
	document.getElementById('forumOpslaan').disabled = false;
	document.getElementById('forumVoorbeeld').disabled = false;
}
function forumCiteren(post) {
	http.abort();
	http.open("GET", "/communicatie/forum/getPost.php?citaat=true&post=" + post, true);
	http.onreadystatechange = function() {
		if (http.readyState == 4) {
			document.getElementById('forumBericht').value += http.responseText;
			//helemaal naar beneden scrollen.
			window.scroll(0, document.body.clientHeight);
		}
	}
	http.send(null);
	//we returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
	//Het werkt dan dus nog wel als javascript uit staat.
	return false;
}
function youtubeDisplay(ytID) {
	var html = '<object width="480" height="385">' +
			'<param name="movie" value="http://www.youtube.com/v/' + ytID + '&autoplay=1&fs=1"></param><param name="allowFullScreen" value="true"></param>' +
			'<embed src="http://www.youtube.com/v/' + ytID + '&autoplay=1&fs=1" type="application/x-shockwave-flash" wmode="transparent" width="480" height="385" allowfullscreen="true"></embed></object>';

	if (document.all) {
		//hier moet een <br /> ofzo voor de <object>-tag, want anders maakt IE de div leeg ipv er iets in te zetten. 
		//2009-02-18 Jieter; dit commentaar was ergens verloren gegaan, maar het blijft een wazige aangelegenheid.
		document.all['youtube' + ytID].innerHTML = '<br />' + html;
	} else {
		document.getElementById('youtube' + ytID).innerHTML = html;
	}
	return false;
}
/**
 *
 * @param {Number} x nummer van de maand
 * @return {String} maand, geprefixt met 0 wanneer nodig
 */
function LZ(x) {
	return(x < 0 || x > 9 ? "" : "0") + x
}

var orig = null;
function togglePasfotos(uids, div) {
	if (orig != null) {
		div.innerHTML = orig;
		orig = null;
	} else {
		http.abort();
		http.open("GET", "/tools/pasfotos.php?string=" + escape(uids), true);
		http.onreadystatechange = function() {
			if (http.readyState == 4) {
				orig = div.innerHTML;
				div.innerHTML = http.responseText;
			}
		}
		http.send(null);
	}
}
//dummy fixPNG
function fixPNG() {
	return false;
}
function uidPreview(fieldname) {
	field = document.getElementById('field_' + fieldname);
	if (field.value.length == 4) {
		http.abort();
		http.open("GET", "/tools/naamlink.php?uid=" + field.value, true);
		http.onreadystatechange = function() {
			if (http.readyState == 4) {
				document.getElementById('preview_' + fieldname).innerHTML = http.responseText;
			}
		}
		http.send(null);
	}
	return null;
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
	textarea = document.getElementById(id);
	http.abort();
	http.open("POST", "/agenda/courant/", true);
	http.onreadystatechange = function() {
		if (http.readyState == 4) {
			document.getElementById(id).value += "\n" + http.responseText;
		}
	}
	http.send(null);
	return null;
}

/* deze js moet wel hier komen, maar op een andere manier blijkbaar
 //javascript voor UBB tag [spoiler]verborgen tekst[/spoiler]
 $(document).ready(function() {
 $(".spoiler_button").click(function spoiler_toggle() {
 $(this).next(".spoiler").toggle('fast');
 });
 });
 */