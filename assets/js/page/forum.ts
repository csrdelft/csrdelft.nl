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
				const { data } = await axios.post(concept.dataset.url, {
					ping: pingValue,
				});
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
if (
	!window.location.hash &&
	window.location.pathname.substr(0, 15) === '/forum/reactie/'
) {
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

// Sla alle ids van forumDraden uit section.forum-deel (alleen op deelfora) op in localStorage voor previous-next functies
try {
	const forumDeel = select<HTMLElement>('section.forum-deel');
	if (forumDeel) {
		localStorage.setItem('forum_draden_ids', forumDeel.dataset.delenList);
	}
} catch (error) {
	console.error(error);
}

// Laad de ids van vorige en volgende forumDraden (alleen op draadjes) uit localStorage voor previous-next functies
try {
	const vorigOnderwerpButton = select<HTMLAnchorElement>('a.vorige-button');
	const volgendOnderwerpButton = select<HTMLAnchorElement>('a.volgende-button');
	const onderwerpRegex = /\d+/g;

	if (volgendOnderwerpButton && vorigOnderwerpButton) {
		const forumDradenIds = localStorage.getItem('forum_draden_ids');

		if (forumDradenIds) {
			// Haal id van huidig onderwerp uit de pathname
			const huidigOnderwerp = window.location.pathname.match(onderwerpRegex);

			if (huidigOnderwerp[0]) {
				const ids = forumDradenIds.split(',');
				const huidigeIndex = ids.indexOf(huidigOnderwerp[0]);

				const vorigeId = ids[huidigeIndex - 1];
				const volgendeId = ids[huidigeIndex + 1];

				if (vorigeId) {
					vorigOnderwerpButton.setAttribute(
						'href',
						window.location.pathname.replace(onderwerpRegex, vorigeId) +
							window.location.hash
					);
				}
				if (volgendeId) {
					volgendOnderwerpButton.setAttribute(
						'href',
						window.location.pathname.replace(onderwerpRegex, volgendeId) +
							window.location.hash
					);
				}
			}
		}
	}
} catch (error) {
	console.error(error);
}

for (const citeerKnop of selectAll<HTMLElement>('a.citeren')) {
	citeerKnop.addEventListener('click', () =>
		forumCiteren(citeerKnop.dataset.citeren)
	);
}

const berichtLinkButtons = selectAll<HTMLButtonElement>('.berichtLinkButton');

// Event listener om forum feed te delen
for (const button of berichtLinkButtons) {
	const berichtLink = button.dataset.berichtLink;
	const nav = navigator;

	button.addEventListener('click', async () => {
		try {
			if ('share' in nav) {
				await navigator.share({
					title: 'C.S.R. Delft Forum',
					url: berichtLink,
				});
			} else if ('clipboard' in nav) {
				await navigator.clipboard.writeText(berichtLink);
				alert('Bericht link is gekopieerd naar het clipboard'); // TODO: kan eleganter
			} else {
				throw new Error('Kan niets met bericht link');
			}
		} catch (err) {
			console.error(err.message);
		}
	});
}

const rssFeedButtons = selectAll<HTMLButtonElement>('.rssFeedButton');
const forumLinkButtons = selectAll<HTMLButtonElement>('.forumLinkButton');

// Event listener om RSS feed te delen
for (const button of rssFeedButtons) {
	const rssLink = button.dataset.rssLink;
	const nav = navigator;

	button.addEventListener('click', async () => {
		console.log('rss', rssLink);
		try {
			if (rssLink === null) {
				await location.assign('/profiel/{{ app.user.uid }}#tokenaanvragen');
			} else if ('share' in nav) {
				await navigator.share({
					title: 'C.S.R. Delft Forum RSS feed',
					url: rssLink,
				});
			} else if ('clipboard' in nav) {
				await navigator.clipboard.writeText(rssLink);
				alert('RSS feed link is gekopieerd naar het clipboard'); // TODO: kan eleganter
			} else {
				throw new Error('Kan niets met RSS feed');
			}
		} catch (err) {
			console.error(err.message);
		}
	});
}

// Event listener om forum feed te delen
for (const button of forumLinkButtons) {
	const forumLink = button.dataset.forumLink;
	const nav = navigator;

	button.addEventListener('click', async () => {
		console.log('forum', forumLink);

		try {
			if ('share' in nav) {
				await navigator.share({
					title: 'C.S.R. Delft Forum',
					url: forumLink,
				});
			} else if ('clipboard' in nav) {
				await navigator.clipboard.writeText(forumLink);
				alert('Forum link is gekopieerd naar het clipboard'); // TODO: kan eleganter
			} else {
				throw new Error('Kan niets met forum link');
			}
		} catch (err) {
			console.error(err.message);
		}
	});
}
