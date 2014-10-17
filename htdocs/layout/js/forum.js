function saveConceptForumBericht() {
	$('#forumConcept').fadeOut();
	if ($('#forumBericht').val() !== $('#forumBericht').attr('origvalue')) {
		$.post('/forum/concept', {
			forumBericht: $('#forumBericht').val()
		}).done(function () {
			$('#forumBericht').attr('origvalue', $('#forumBericht').val());
		}).fail(alert);
	}
}

$(document).ready(function ($) {

	var toggleShowSaveConcept = function () {
		if ($('#forumBericht').val() !== $('#forumBericht').attr('origvalue')) {
			$('#forumConcept').fadeIn();
		} else {
			$('#forumConcept').fadeOut();
		}
	};
	var autosave;
	$('#forumBericht').focusin(function () {
		autosave = setInterval(toggleShowSaveConcept, 1000);
	});
	$('#forumBericht').focusout(function () {
		clearInterval(autosave);
	});

	//naar juiste forumreactie scrollen door hash toe te voegen
	if (!window.location.hash && window.location.pathname.substr(0, 15) == '/forum/reactie/') {
		var reactieid = parseInt(window.location.pathname.substr(15), 10);
		window.location.hash = '#' + reactieid;
	}

	//klein beetje terug omhoog scrollen bij ongelezen lijn
	if (window.location.hash == '#ongelezen') {
		try {
			$(window).scrollTo('#ongelezen', 1, {
				offset: {
					top: -100,
					left: 0
				}
			});
		} catch (e) {
			// missing scrollTo
		}
	}

	$('#forumBericht').each(function () {

		$(this).markItUp(mySettings); // mySettings located in set.js

		if ($(this).hasClass('extern')) {
			$('#meldingen').prepend('<div id="extern_melding"><strong>Openbaar forum</strong><br />Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br />Zet [prive] en [/prive] om uw persoonlijke contactgegevens in het bericht.</div>');
		}
	}).keyup(function (event) {
		var textarea = $(this);

		if (event.keyCode == 13) { //enter == 13
			if (/\[.*\]/.test(textarea.val())) {
				//detected ubb tag use, trigger preview and display message.
				ubbPreview('forumBericht', 'berichtPreview');

				if ($('#ubb_melding').length == 0) {
					textarea.before('<div id="ubb_melding">UBB gevonden:<br /> controleer het voorbeeld.</div>');

					$('#ubb_melding').click(function () {
						$('#ubbhulpverhaal').toggle();
					});
				}
			}
		}
		if ($('#ketzer_melding').length == 0 && /ketzer/.test(textarea.val())) {
			$('#meldingen').prepend('<div id="ketzer_melding">Ketzer hebben?<br /><a href="/actueel/groepen/Ketzers" target="_blank">&raquo; Maak er zelf een aan.</a></div>');
		}
	});

	$('.togglePasfoto').each(function () {
		$(this).click(function () {
			var parts = $(this).attr('id').substr(1).split('-');
			var pasfoto = $('#p' + parts[1]);
			if (pasfoto.html() == '') {
				pasfoto.html('<img src="/tools/pasfoto/' + parts[0] + '.png" class="lidfoto" />');
			}
			if (pasfoto.hasClass('verborgen')) {
				pasfoto.toggleClass('verborgen');
				$(this).html('');
			}
		});
	});

	$('td.auteur').hoverIntent(
			function () {
				$(this).find('a.forummodknop').fadeIn();
			},
			function () {
				$(this).find('a.forummodknop').fadeOut();
			}
	);
});

var orig = null;
function togglePasfotos(uids, div) {
	if (orig != null) {
		div.innerHTML = orig;
		orig = null;
	} else {
		http.abort();
		http.open("GET", "/tools/pasfotos.php?string=" + escape(uids), true);
		http.onreadystatechange = function () {
			if (http.readyState == 4) {
				orig = div.innerHTML;
				div.innerHTML = http.responseText;
			}
		};
		http.send(null);
	}
}
/*
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 */
var bewerkDiv = null;
var bewerkDivInnerHTML = null;
function forumBewerken(postId) {
	http.abort();
	http.open("POST", "/forum/tekst/" + postId, true);
	http.onreadystatechange = function () {
		if (http.readyState == 4) {
			if (document.getElementById('forumEditForm')) {
				restorePost();
			}
			bewerkDiv = document.getElementById('post' + postId);
			bewerkDivInnerHTML = bewerkDiv.innerHTML;
			bewerkForm = '<form id="forumEditForm" class="Formulier InlineForm" action="/forum/bewerken/' + postId + '" method="post">';
			bewerkForm += '<div class="cursief">Bericht bewerken</div>Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]<br />';
			bewerkForm += '<div id="bewerkPreview" class="preview forumBericht"></div>';
			bewerkForm += '<textarea name="forumBericht" id="forumBewerkBericht" class="tekst" rows="8"></textarea>';
			bewerkForm += 'Reden van bewerking: <input type="text" name="reden" id="forumBewerkReden"/><br /><br />';
			bewerkForm += '<a class="knop float-right" onclick="$(\'#ubbhulpverhaal\').toggle();" title="Opmaakhulp weergeven">Opmaak</a>';
			bewerkForm += '<a class="knop float-right vergroot" data-vergroot="#forumBewerkBericht" title="Vergroot het invoerveld">&uarr;&darr;</a>';
			bewerkForm += '<input type="button" value="Opslaan" onclick="submitPost();" /> ' +
					'<input type="button" value="Voorbeeld" onclick="ubbPreview(\'forumBewerkBericht\', \'bewerkPreview\');" /> ' +
					'<input type="button" value="Annuleren" onclick="restorePost();" />';
			bewerkForm += '</form>';
			bewerkDiv.innerHTML = bewerkForm;
			document.getElementById('forumBewerkBericht').value = http.responseText;
			$('#forumBewerkBericht').autosize();
			$('#forumBewerkBericht').markItUp(mySettings); // mySettings located in set.js
			//invoerveldjes van het normale toevoegformulier even uitzetten.
			document.getElementById('forumBericht').disabled = true;
			document.getElementById('forumOpslaan').disabled = true;
			document.getElementById('forumVoorbeeld').disabled = true;
		}
	};
	http.send(null);
	return false;
}
function forumCiteren(postId) {
	http.abort();
	http.open("POST", "/forum/citeren/" + postId, true);
	http.onreadystatechange = function () {
		if (http.readyState == 4) {
			document.getElementById('forumBericht').value += http.responseText;
			$(window).scrollTo('#reageren');
		}
	};
	http.send(null);
	//we returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
	//Het werkt dan dus nog wel als javascript uit staat.
	return false;
}
function restorePost() {
	bewerkDiv.innerHTML = bewerkDivInnerHTML;
	document.getElementById('forumBericht').disabled = false;
	document.getElementById('forumOpslaan').disabled = false;
	document.getElementById('forumVoorbeeld').disabled = false;
}
function submitPost() {
	var form = $('#forumEditForm');
	var jqXHR = $.ajax({
		type: 'POST',
		cache: false,
		url: form.attr('action'),
		data: form.serialize()
	});
	jqXHR.done(function (data, textStatus, jqXHR) {
		restorePost();
		dom_update(data);
	});
	jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
		alert(textStatus);
	});
}