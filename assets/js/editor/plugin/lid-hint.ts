import { suggest, Suggester } from 'prosemirror-suggest';
import axios, { CancelToken } from 'axios';
import { EditorView } from 'prosemirror-view';

let selectedIndex = 0;
let lidList: NaamSuggestie[] = [];
let cancel = axios.CancelToken.source();

interface NaamSuggestie {
	icon: string;
	url: string;
	label: string;
	value: string;
	uid: string;
}

async function search(term: string, cancelToken?: CancelToken): Promise<NaamSuggestie[]> {
	if (!term || term.length === 1) {
		return [];
	} else {
		const response = await axios.get('/tools/naamsuggesties?vorm=user&zoekin=voorkeur&q=' + encodeURI(term), {
			cancelToken,
		});

		return response.data;
	}
}

const updateLidHintsPosition = (view: EditorView, pos: { left: number; bottom: number }) => {
	const lidHints = document.querySelector<HTMLDivElement>('.lid-hints');
	if (!lidHints) return;

	const viewPos = view.dom.parentElement.getBoundingClientRect();
	lidHints.style.left = pos.left - viewPos.left + 'px';
	lidHints.style.top = pos.bottom - viewPos.top + 'px';
};

const createLidHints = (
	command,
	view: EditorView,
	pos: { left: number; right: number; top: number; bottom: number }
) => {
	Array.from(document.querySelectorAll('.lid-hints')).forEach((hint) => hint.remove());

	const lidHints = document.createElement('div');
	lidHints.classList.add('lid-hints', 'list-group');
	lidHints.style.position = 'absolute';

	for (const lid of lidList) {
		const lidDiv = lidHints.appendChild(document.createElement('div'));
		lidDiv.classList.add('list-group-item', 'list-group-item-action');

		lidDiv.addEventListener('click', () => {
			command(lid);
		});
		lidDiv.textContent = lid.value;
	}

	view.dom.parentElement.appendChild(lidHints);

	updateLidHintsPosition(view, pos);
	updateLidHints();
};

const removeLidHints = () => {
	const lidHints = document.querySelector('.lid-hints');

	if (lidHints) lidHints.remove();
};

const updateLidHints = () => {
	const lidHints = document.querySelector('.lid-hints');

	for (const hint of Array.from(lidHints.children)) {
		hint.classList.remove('active');
	}

	const lidHint = lidHints.children.item(selectedIndex);

	if (lidHint) lidHint.classList.add('active');
};

const suggestLeden: Suggester = {
	noDecorations: true,
	char: '@',
	name: 'lid-hint',
	appendText: '',

	keyBindings: {
		ArrowUp: () => {
			selectedIndex = (selectedIndex - 1 + lidList.length) % lidList.length;
			updateLidHints();
		},
		ArrowDown: () => {
			selectedIndex = (selectedIndex + 1) % lidList.length;
			updateLidHints();
		},
		// Enter wordt niet opgepikt.
		Enter: ({ command, event }) => {
			event.preventDefault();
			command(lidList[selectedIndex]);
		},
		Tab: ({ command, event }) => {
			event.preventDefault();
			command(lidList[selectedIndex]);
		},
		Esc: () => {
			removeLidHints();
		},
	},

	onChange: async ({ command, queryText, range, view }) => {
		updateLidHintsPosition(view, view.coordsAtPos(range.end));

		cancel.cancel();
		cancel = axios.CancelToken.source();
		try {
			lidList = await search(queryText.full, cancel.token);
			selectedIndex = 0;
			createLidHints(command, view, view.coordsAtPos(range.end));
		} catch {
			// noop, cancelled
		}
	},

	onExit: () => {
		cancel.cancel();
		removeLidHints();
		lidList = [];
		selectedIndex = 0;
	},

	// Create a  function that is passed into the change, exit and keybinding handlers.
	// This is useful when these handlers are called in a different part of the app.
	createCommand: ({ match, view }) => {
		return (lid: NaamSuggestie) => {
			if (!lid) {
				return;
			}

			const tr = view.state.tr;
			const { from, to } = match.range;
			tr.replaceWith(from, to, view.state.schema.nodes.lid.createAndFill({ uid: lid.uid, naam: lid.value }));
			view.dispatch(tr);
		};
	},
};

export const lidHintPlugin = suggest(suggestLeden);
