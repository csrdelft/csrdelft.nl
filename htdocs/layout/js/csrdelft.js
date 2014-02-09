/*
 * csrdelft.nl javascript libje...
 */

$(document).ready(function() {
	ShowMenu(menu_active);
	init_visitekaartjes();
});

function form_reset(form) {
	$(form).find('.regular').each(function() {
		if ($(this).val() !== $(this).attr('origvalue')) {
			$(this).val($(this).attr('origvalue'));
		}
	});
}

/**
 * Aan-/afmelden m.b.v. een ketzer
 * 
 * @requires jQuery
 * @param url
 * @param ketzer
 */
function ketzer_ajax(url, ketzer) {
	jQuery(ketzer + ' .aanmelddata').html('Aangemeld:<br /><img src="http://plaetjes.csrdelft.nl/layout/loading-arrows.gif" />');
	jQuery.ajax({
		type: 'GET',
		cache: false,
		url: url,
		data: '',
		success: function(response) {
			var html = jQuery.parseHTML(response);
			jQuery('.ubb_maaltijd').each(function() {
				if (jQuery(this).attr('id') === jQuery(html).attr('id')) {
					jQuery(this).replaceWith(response);
				}
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			jQuery(ketzer + ' .aanmelddata').html('<span style="color: red; font-weight: bold;">Error:</span><br />' + errorThrown);
			alert(errorThrown);
		}
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

function init_visitekaartjes() {
	$('.visite').each(function() {
		if ($(this).hasClass('init')) {
			$(this).removeClass('init'); // 1 handler
			$(this).hoverIntent(function() {
				var id = $(this).attr('id');
				id = id.replace('v', 'k');
				$('#' + id).fadeIn();
			});
		}
	});
	$('.visitekaartje').each(function() {
		if ($(this).hasClass('init')) {
			$(this).removeClass('init'); // 1 handler
			$(this).mouseleave(function() {
				$(this).fadeOut();
			});
		}
	});
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
	http.open("GET", "/agenda/courant/", true);
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