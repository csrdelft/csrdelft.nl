import axios, { CancelToken } from 'axios';
import { EditorView } from 'prosemirror-view';
import autocomplete, {
	ActionKind,
	FromTo,
	Options,
} from 'prosemirror-autocomplete';

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

async function search(
	term: string,
	cancelToken?: CancelToken
): Promise<NaamSuggestie[]> {
	if (!term || term.length === 1) {
		return [];
	} else {
		const response = await axios.get(
			'/tools/naamsuggesties?vorm=user&zoekin=voorkeur&q=' + encodeURI(term),
			{ cancelToken }
		);

		return response.data;
	}
}

const updateLidHintsPosition = (
	view: EditorView,
	pos: { left: number; bottom: number }
) => {
	const lidHints = document.querySelector<HTMLDivElement>('.lid-hints');
	if (!lidHints) return;

	const viewPos = view.dom.parentElement.getBoundingClientRect();
	lidHints.style.left = pos.left - viewPos.left + 'px';
	lidHints.style.top = pos.bottom - viewPos.top + 'px';
};

const createLidHints = (view: EditorView, range: FromTo) => {
	Array.from(document.querySelectorAll('.lid-hints')).forEach((hint) =>
		hint.remove()
	);

	const lidHints = document.createElement('div');
	lidHints.classList.add('lid-hints', 'list-group');
	lidHints.style.position = 'absolute';

	for (const lid of lidList) {
		const lidDiv = lidHints.appendChild(document.createElement('div'));
		lidDiv.classList.add('list-group-item', 'list-group-item-action');

		lidDiv.addEventListener('click', () => {
			insertLid(view, range, lid);
		});
		lidDiv.textContent = lid.value;
	}

	view.dom.parentElement.appendChild(lidHints);

	updateLidHintsPosition(view, view.coordsAtPos(range.to));
	updateLidHints();
};

const removeLidHints = () => {
	const lidHints = document.querySelector('.lid-hints');

	if (lidHints) lidHints.remove();
};

const updateLidHints = () => {
	const lidHints = document.querySelector('.lid-hints');

	if (!lidHints) return;

	for (const hint of Array.from(lidHints.children)) {
		hint.classList.remove('active');
	}

	const lidHint = lidHints.children.item(selectedIndex);

	if (lidHint) lidHint.classList.add('active');
};

const insertLid = (view: EditorView, range: FromTo, lid?: NaamSuggestie) => {
	if (!lid) {
		return false;
	}

	const { from, to } = range;
	view.dispatch(
		view.state.tr.replaceWith(
			from,
			to,
			view.state.schema.nodes.lid.createAndFill({
				uid: lid.uid,
				naam: lid.value,
			})
		)
	);

	onExit();

	return true;
};

const onExit = () => {
	lidList = [];
	selectedIndex = 0;
	removeLidHints();

	cancel.cancel();
};

const triggerLidHints = async (filter: string, view, range: FromTo) => {
	cancel.cancel();
	cancel = axios.CancelToken.source();
	try {
		lidList = await search(filter, cancel.token);
		selectedIndex = 0;
		createLidHints(view, range);
	} catch {
		// noop, cancelled
	}
};

// Create autocomplete with triggers and specified handers:
const options: Partial<Options> = {
	triggers: [{ name: 'mention', trigger: '@' }],
	onOpen: ({ view, range, trigger }) => {
		triggerLidHints(trigger, view, range);

		return true;
	},
	onArrow: ({ kind }) => {
		switch (kind) {
			case ActionKind.down:
				selectedIndex = (selectedIndex + 1) % lidList.length;
				updateLidHints();
				break;
			case ActionKind.up:
				selectedIndex = (selectedIndex - 1 + lidList.length) % lidList.length;
				updateLidHints();
				break;
		}

		return true;
	},
	onFilter: ({ view, filter, range }) => {
		triggerLidHints(filter, view, range);

		return true;
	},
	onEnter: ({ view, range }) => {
		insertLid(view, range, lidList[selectedIndex]);

		return true;
	},
	onClose: () => {
		onExit();

		return true;
	},
};

export const lidHintPlugin = autocomplete(options);
