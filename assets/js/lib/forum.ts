import $ from 'jquery';
import { init } from '../ctx';
import { domUpdate } from './domUpdate';
import { html, throwError } from './util';
import axios from 'axios';
import { Fragment, NodeType, Slice } from 'prosemirror-model';
import { EditorView } from 'prosemirror-view';
import { EditorSchema } from '../editor/schema';
import { select, selectAll } from '../lib/dom';

export function toggleForumConceptBtn(enable: boolean): void {
	const conceptButton = document.getElementById(
		'forumConcept'
	) as HTMLButtonElement;

	if (typeof enable === 'undefined') {
		conceptButton.disabled = !conceptButton.disabled;
	} else {
		conceptButton.disabled = !enable;
	}
}

export function saveConceptForumBericht(): void {
	toggleForumConceptBtn(false);

	const formulier = document.getElementById('forumForm') as HTMLFormElement;
	const concept = document.querySelector<HTMLButtonElement>('#forumConcept');
	const url = concept.dataset.url;
	if (!url) {
		throw new Error('concept knop heeft geen data-url');
	}

	axios.post(url, new FormData(formulier));

	setTimeout(toggleForumConceptBtn, 3000);
}

let bewerkContainer: HTMLElement | null = null;
let bewerkContainerInnerHTML: string | null = null;
// Houdt een verwijzing naar de standaard editor in deze pagina voor bij bewerken.
let oldEditor: EditorView<EditorSchema> | null = null;

/**
 * @see inline in forumBewerken
 */
function restorePost() {
	if (!bewerkContainer || !bewerkContainerInnerHTML) {
		// niets te restoren
		return;
	}

	window.currentEditor = oldEditor;

	bewerkContainer.innerHTML = bewerkContainerInnerHTML;
	$('#bewerk-melding').slideUp(200, function () {
		$(this).remove();
	});
	$('#forumPosten').css('visibility', 'visible');
}

async function submitPost(event: Event, form: HTMLFormElement) {
	event.preventDefault();

	try {
		const response = await axios.post<string>(form.action, new FormData(form));
		restorePost();
		domUpdate(response.data);
	} catch (error) {
		throwError(error);
	}
}

/**
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 *
 * @see templates/forum/partial/post_lijst.html.twig
 */
export async function forumBewerken(postId: string): Promise<false> {
	const response = await axios.post<unknown>(`/forum/tekst/${postId}`);

	if (document.getElementById('forumEditForm')) {
		restorePost();
	}

	bewerkContainer = document.getElementById('post' + postId);
	bewerkContainerInnerHTML = bewerkContainer.innerHTML;

	const berichtInput = html<HTMLInputElement>`<input
		type="hidden"
		name="forumBericht"
		id="forumBewerkenBericht"
	/>`;
	berichtInput.value = JSON.stringify(response.data);

	bewerkContainer.innerHTML = '';
	bewerkContainer.appendChild(html`
		<form
			id="forumEditForm"
			class="ForumFormulier"
			action="/forum/bewerken/${postId}"
			method="post"
		>
			${berichtInput}
			<div
				id="editor"
				class="pm-editor"
				data-prosemirror-doc="forumBewerkenBericht"
			></div>
			<div class="row mb-3">
				<label class="col-sm-3">Reden van bewerking:</label>
				<div class="col-sm-9">
					<input
						type="text"
						name="reden"
						id="forumBewerkReden"
						class="form-control"
					/>
				</div>
			</div>
			<input type="submit" class="opslaan btn btn-primary" value="Opslaan" />
			<input
				type="button"
				class="annuleren btn btn-secondary"
				value="Annuleren"
			/>
		</form>
	`);

	const form = bewerkContainer.querySelector('form');
	form.addEventListener('submit', (e) => submitPost(e, form));
	bewerkContainer
		.querySelector('input.annuleren')
		.addEventListener('click', restorePost);

	oldEditor = window.currentEditor;

	init(bewerkContainer);

	$(bewerkContainer).parent().children('.auteur:first')
		.append(`<div id="bewerk-melding" class="alert alert-warning">
Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]
</div>`);
	$('#bewerk-melding').slideDown(200);

	const forumPosten = document.getElementById('forumPosten');
	// forumPosten bestaat niet op /forum/wacht
	if (forumPosten) {
		forumPosten.style.visibility = 'hidden';
	}

	return false;
}

export async function forumCiteren(postId: string): Promise<false> {
	const response = await axios.post<{
		van: string;
		naam: string;
		content: unknown;
	}>('/forum/citeren/' + postId);

	const { van, naam, content } = response.data;

	const editor = window.currentEditor;
	const citaat: NodeType = editor.state.schema.nodes.citaat;

	// Maak een slice met de citaat en een lege paragraaf, zodat er makkelijk doorgetyped kan worden.
	const citaatNode = citaat.create(
		{ van, naam },
		Fragment.fromJSON(editor.state.schema, content)
	);
	const paragraphNode = editor.state.schema.nodes.paragraph.create();
	const slice = new Slice(
		Fragment.fromArray([citaatNode, paragraphNode]),
		0,
		0
	);

	window.currentEditor.dispatch(editor.state.tr.replaceSelection(slice));

	$(window).scrollTo('#reageren');
	// We returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
	// Het werkt dan dus nog wel als javascript uit staat.
	return false;
}

export const slaOpForumDraden = async (
	parentSelector = 'section.forum-deel'
) => {
	// Sla alle ids van forumDraden uit section.forum-deel (alleen op deelfora) op in localStorage voor previous-next functies
	try {
		await new Promise<void>((resolve, reject) => {
			const forumDeel = select<HTMLElement>(parentSelector);
			if (forumDeel) {
				localStorage.setItem('forum_draden', forumDeel.dataset.dradenList);
				localStorage.setItem(
					'forum_vorige_url',
					forumDeel.dataset.dradenVorigeUrl
				);
				localStorage.setItem(
					'forum_volgende_url',
					forumDeel.dataset.dradenVolgendeUrl
				);
				resolve();
			} else {
				reject('Forum deel sectie niet gevonden');
			}
		});
	} catch (error) {
		console.error(error);
	}
};

export const laadForumDraden = () => {
	// Laad de ids van vorige en volgende forumDraden (alleen op draadjes) uit localStorage voor previous-next functies
	try {
		const vorigOnderwerpButton = select<HTMLAnchorElement>('a.vorige-button');
		const volgendOnderwerpButton =
			select<HTMLAnchorElement>('a.volgende-button');
		const onderwerpRegex = /\d+/g;

		if (volgendOnderwerpButton && vorigOnderwerpButton) {
			const draden = localStorage.getItem('forum_draden');
			const vorigeURL = localStorage.getItem('forum_vorige_url');
			const volgendeURL = localStorage.getItem('forum_volgende_url');

			if (draden) {
				// Haal id van huidig onderwerp uit de pathname
				const huidigOnderwerp = window.location.pathname.match(onderwerpRegex);

				if (huidigOnderwerp[0]) {
					type ForumDraad = { id: number; titel: string };
					const dradenArray: ForumDraad[] = JSON.parse(draden);
					const huidigeIndex = dradenArray.findIndex(
						(d) => String(d.id) === huidigOnderwerp[0]
					);

					if (huidigeIndex === -1) throw new Error('Index niet gevonden');

					const vorigeDraad = dradenArray[huidigeIndex - 1];
					const volgendeDraad = dradenArray[huidigeIndex + 1];

					if (vorigeDraad) {
						vorigOnderwerpButton.setAttribute(
							'href',
							window.location.pathname.replace(
								onderwerpRegex,
								String(vorigeDraad.id)
							) + window.location.hash
						);
						vorigOnderwerpButton.querySelector('strong').textContent =
							vorigeDraad.titel;
					} else {
						vorigOnderwerpButton.setAttribute('href', vorigeURL);
					}

					if (volgendeDraad) {
						volgendOnderwerpButton.setAttribute(
							'href',
							window.location.pathname.replace(
								onderwerpRegex,
								String(volgendeDraad.id)
							) + window.location.hash
						);
						volgendOnderwerpButton.querySelector('strong').textContent =
							volgendeDraad.titel;
					} else {
						volgendOnderwerpButton.setAttribute('href', volgendeURL);
					}
				}
			}
		}
	} catch (error) {
		console.error(error);
	}
};
