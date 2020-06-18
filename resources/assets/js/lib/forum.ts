import $ from 'jquery';
import {init} from '../ctx';
import {domUpdate} from './domUpdate';

export function toggleForumConceptBtn(enable: boolean) {
	const $concept = $('#forumConcept');
	if (typeof enable === 'undefined') {
		$concept.attr('disabled', String(!Boolean($concept.prop('disabled'))));
	} else {
		$concept.attr('disabled', String(!enable));
	}
}

export function saveConceptForumBericht() {
	toggleForumConceptBtn(false);
	const $concept = $('#forumConcept');
	const $textarea = $('#forumBericht');
	const $titel = $('#nieuweTitel');
	if ($textarea.val() !== $textarea.attr('origvalue')) {
		$.post($concept.attr('data-url')!, {
			forumBericht: $textarea.val(),
			titel: ($titel.length === 1 ? $titel.val() : ''),
		}).done(() => {
			$textarea.attr('origvalue', String($textarea.val()));
		}).fail((error) => {
			alert(error);
		});
	}
	setTimeout(toggleForumConceptBtn, 3000);
}

let bewerkContainer: JQuery | null = null;
let bewerkContainerInnerHTML: string | null = null;

/**
 * @see inline in forumBewerken
 */
function restorePost() {
	bewerkContainer!.html(bewerkContainerInnerHTML!);
	$('#bewerk-melding').slideUp(200, function () {
		$(this).remove();
	});
	$('#forumPosten').css('visibility', 'visible');
}

function submitPost(event: Event) {
	event.preventDefault();
	const form = $('#forumEditForm');
	$.ajax({
		type: 'POST',
		cache: false,
		url: form.attr('action'),
		data: form.serialize(),
	}).done((data) => {
		restorePost();
		domUpdate(data);
	}).fail((jqXHR) => alert(jqXHR.responseJSON));
}

/**
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 *
 * @see blade_templates/forum/partial/post_lijst.blade.php
 */
export function forumBewerken(postId: string) {
	$.ajax({
		url: '/forum/tekst/' + postId,
		method: 'POST',
	}).done((data) => {
		if (document.getElementById('forumEditForm')) {
			restorePost();
		}
		bewerkContainer = $('#post' + postId);
		bewerkContainerInnerHTML = bewerkContainer.html();
		bewerkContainer.html(`
<form id="forumEditForm" class="ForumFormulier" action="/forum/bewerken/${postId}" method="post">
	<div id="preview_forumBewerkBericht" class="bbcodePreview forumBericht"></div>
	<textarea name="forumBericht" id="forumBewerkBericht" data-bbpreview="forumBewerkBericht" class="FormElement BBCodeField" rows="8"></textarea>
	Reden van bewerking: <input type="text" name="reden" id="forumBewerkReden"/>
	<br />
	<br />
	<div class="float-right"><a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a></div>
	<input type="submit" class="opslaan btn btn-primary" value="Opslaan" />
	<input type="button" class="voorbeeld btn btn-secondary" value="Voorbeeld" data-bbpreview-btn="forumBewerkBericht" />
	<input type="button" class="annuleren btn btn-secondary" value="Annuleren" />
</form>`);
		bewerkContainer.find('form').on('submit', submitPost);
		bewerkContainer.find('input.annuleren').on('click', restorePost);

		init(bewerkContainer.get(0));

		$('#forumBewerkBericht').val(data);
		$(bewerkContainer).parent().children('.auteur:first')
			.append(`<div id="bewerk-melding" class="alert alert-warning">
Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]
</div>`);
		$('#bewerk-melding').slideDown(200);
		$('#forumPosten').css('visibility', 'hidden');
	});
	return false;
}

export function forumCiteren(postId: string) {
	$.ajax({
		url: '/forum/citeren/' + postId,
		method: 'POST',
	}).done((data) => {
		const bericht = $('#forumBericht');
		bericht.val(bericht.val() + data);
		$(window).scrollTo('#reageren');
	});
	// We returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
	// Het werkt dan dus nog wel als javascript uit staat.
	return false;
}
