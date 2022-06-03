import $ from 'jquery';
import axios from 'axios';
import { domUpdate } from '../lib/domUpdate';
import { forumCiteren } from '../lib/forum';
import hoverintent from 'hoverintent';
import { select, selectAll } from '../lib/dom';

try {
	const textarea = select<HTMLTextAreaElement>('textarea#forumBericht');
	const concept = select<HTMLElement>('#forumConcept');

	// The last value that we pinged
	let lastPing = false;

	/*var ping = */
	setInterval(async () => {
		const pingValue = textarea.value !== textarea.getAttribute('origvalue');
		if (pingValue || lastPing) {
			try {
				const { data } = await axios.post(concept.dataset.url, { ping: pingValue });
				domUpdate(data);
				lastPing = pingValue;
			} catch (e) {
				// Herlaad de pagina als dit niet lukt
				window.location.reload();
			}
		}
	}, 60000);
	/*var autosave;
	 $textarea.focusin(function () {
	 autosave = setInterval(saveConceptForumBericht, 3000);
	 });
	 $textarea.focusout(function () {
	 clearInterval(autosave);
	 });*/
} catch (e) {
	// Geen edit veld hier
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

selectAll('.auteur').forEach((auteur) => {
	const forummodKnoppen = selectAll<HTMLElement>('a.forummodknop', auteur);

	hoverintent(
		auteur,
		() => forummodKnoppen.forEach((el) => (el.style.opacity = '1')),
		() => forummodKnoppen.forEach((el) => (el.style.opacity = '0'))
	);
});

for (const citeerKnop of selectAll<HTMLElement>('a.citeren')) {
	citeerKnop.addEventListener('click', () => forumCiteren(citeerKnop.dataset.citeren));
}
