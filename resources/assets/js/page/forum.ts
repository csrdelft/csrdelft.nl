import $ from 'jquery';
import {domUpdate} from '../lib/domUpdate';
import {forumCiteren} from '../lib/forum';

$(() => {
	const $textarea = $('#forumBericht');
	const $concept = $('#forumConcept');

	// The last value that we pinged
	let lastPing = false;
	if ($concept.length === 1) {

		/*var ping = */
		setInterval(() => {
			const pingValue = $textarea.val() !== $textarea.attr('origvalue');
			if (pingValue || lastPing) {
				$.post($concept.attr('data-url')!, {
					ping: pingValue,
				}).done(domUpdate);
				lastPing = pingValue;
			}
		}, 60000);
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
		const reactieid = parseInt(window.location.pathname.substr(15), 10);
		window.location.hash = '#' + reactieid;
	}

	$('#nieuweTitel').on('focusin', () => $('#draad-melding').slideDown(200));

	$('.togglePasfoto').on('click', function () {
		$(this).parent().find('.forumpasfoto').toggleClass('verborgen');
	});

	$('.auteur').hoverIntent(
		function (this: any) {
			$(this).find('a.forummodknop').css('opacity', '1');
		},
		function (this: any) {
			$(this).find('a.forummodknop').css('opacity', '0');
		},
	);

	$('a.citeren').on('click', function () {
		const postid = $(this).attr('data-citeren')!;
		forumCiteren(postid);
	});
});
