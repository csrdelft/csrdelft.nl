function toggleForumConceptBtn(enable) {
	var $concept = $('#forumConcept');
	if (typeof enable === 'undefined') {
		$concept.attr('disabled', !$concept.prop('disabled'));
	} else {
		$concept.attr('disabled', !enable);
	}
}

function saveConceptForumBericht() {
	toggleForumConceptBtn(false);
	var $concept = $('#forumConcept');
	var $textarea = $('#forumBericht');
	var $titel = $('#nieuweTitel');
	if ($textarea.val() !== $textarea.attr('origvalue')) {
		$.post($concept.attr('data-url'), {
			forumBericht: $textarea.val(),
			titel: ($titel.length === 1 ? $titel.val() : '')
		}).done(function () {
			$textarea.attr('origvalue', $textarea.val());
		}).fail(alert);
	}
	setTimeout(toggleForumConceptBtn, 3000);
}

var bewerkContainer = null;
var bewerkContainerInnerHTML = null;
function restorePost() {
	bewerkContainer.innerHTML = bewerkContainerInnerHTML;
	$('#bewerk-melding').slideUp(200, remove);
	$('#forumPosten').css('visibility', 'visible');
}

/**
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 */
function forumBewerken(postId) {
	$.ajax({
		url: '/forum/tekst/' + postId,
		method: 'POST'
	}).done(function(data) {
		if (document.getElementById('forumEditForm')) {
			restorePost();
		}
		bewerkContainer = document.getElementById('post' + postId);
		bewerkContainerInnerHTML = bewerkContainer.innerHTML;
		var bewerkForm = '<form id="forumEditForm" class="Formulier" action="/forum/bewerken/' + postId + '" method="post">';
		bewerkForm += '<div id="bewerkPreview" class="preview forumBericht"></div>';
		bewerkForm += '<textarea name="forumBericht" id="forumBewerkBericht" class="FormElement BBCodeField" rows="8"></textarea>';
		bewerkForm += 'Reden van bewerking: <input type="text" name="reden" id="forumBewerkReden"/><br /><br />';
		bewerkForm += '<div class="float-right"><a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a></div>';
		bewerkForm += '<input type="button" value="Opslaan" onclick="submitPost();" /> <input type="button" value="Voorbeeld" onclick="CsrBBPreview(\'forumBewerkBericht\', \'bewerkPreview\');" /> <input type="button" value="Annuleren" onclick="restorePost();" />';
		bewerkForm += '</form>';
		bewerkContainer.innerHTML = bewerkForm;
		var $forumBewerkBericht = $('#forumBewerkBericht');
		$forumBewerkBericht.val(data);
		$forumBewerkBericht.autosize();
		$forumBewerkBericht.markItUp(CsrBBcodeMarkItUpSet); // CsrBBcodeMarkItUpSet is located in: /layout/js/markitup/sets/bbcode/set.js
		$(bewerkContainer).parent().children('td.auteur:first').append('<div id="bewerk-melding">Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]</div>');
		$('#bewerk-melding').slideDown(200);
		$('#forumPosten').css('visibility', 'hidden');
	});
	return false;
}

function forumCiteren(postId) {
	$.ajax({
		url: '/forum/citeren/' + postId,
		method: 'POST'
	}).done(function (data) {
		$('#forumBericht').append(data);
		$(window).scrollTo('#reageren');
	});
	// We returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
	// Het werkt dan dus nog wel als javascript uit staat.
	return false;
}

function submitPost() {
	var form = $('#forumEditForm');
	$.ajax({
		type: 'POST',
		cache: false,
		url: form.attr('action'),
		data: form.serialize()
	}).done(function (data) {
		restorePost();
		dom_update(data);
	}).fail(function (jqXHR) {
		alert(jqXHR.responseJSON);
	});
}

$(document).ready(function ($) {

	var $textarea = $('#forumBericht');
	var $concept = $('#forumConcept');

	if ($concept.length === 1) {
		var updateReageren = function () {
			$.post($concept.attr('data-url'), {
				ping: ($textarea.val() !== $textarea.attr('origvalue'))
			}).done(dom_update).fail(alert);
		};
		/*var ping = */setInterval(updateReageren, 60000);
		/*var autosave;
		 $textarea.focusin(function () {
		 autosave = setInterval(saveConceptForumBericht, 3000);
		 });
		 $textarea.focusout(function () {
		 clearInterval(autosave);
		 });*/
	}

	// naar juiste forumreactie scrollen door hash toe te voegen
	if (!window.location.hash && window.location.pathname.substr(0, 15) === '/forum/reactie/') {
		var reactieid = parseInt(window.location.pathname.substr(15), 10);
		window.location.hash = '#' + reactieid;
	}

	$textarea.keyup(function (event) {
		if (event.keyCode === 13) { // enter
			CsrBBPreview('forumBericht', 'berichtPreview');
		}
	});

	var $nieuweTitel = $('#nieuweTitel');

	if ($nieuweTitel.length !== 0) {
		$nieuweTitel.focusin(function () {
			$('#draad-melding').slideDown(200);
		});
		$nieuweTitel.focusout(function () {
			$('#draad-melding').slideUp(200);
		});
	}

	$('.togglePasfoto').each(function () {
		$(this).click(function () {
			var parts = $(this).attr('id').substr(1).split('-');
			var pasfoto = $('#p' + parts[1]);
			if (pasfoto.html() === '') {
				pasfoto.html('<img src="/tools/pasfoto/' + parts[0] + '.png" class="pasfoto" />');
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

	$('a.citeren').click(function () {
		var postid = $(this).attr('data-citeren');
		forumCiteren(postid);
	});
});
