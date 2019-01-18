const {Textcomplete, Textarea} = require('textcomplete');

export function activeerLidHints(textarea: HTMLElement) {
	const editor = new Textarea(textarea);
	const textcomplete = new Textcomplete(editor);

	textcomplete.register([{
		// @...
		match: /(^|\s|])@((?:[^ ]+ ?){1,5})$/,
		replace (data: any) {
			return '$1[lid=' + data.label + ']';
		},
		search,
		template,
	}, {
		// [citaat=... of [lid=...
		index: 3,
		match: /(^|\s|])\[(citaat|lid)=(?:[0-9]{4}|([^\]]+))$/,
		search,
		template,
		replace (data: any) {
			return '$1[$2=' + data.label;
		},
	}]);
	textcomplete.on('rendered', () => {
		if (textcomplete.dropdown.items.length >= 1) {
			// Activeer eerste keuze standaard
			textcomplete.dropdown.items[0].activate();
		}
	});
}

function search(term: string, callback: (data: any) => void) {
	if (!term || term.length === 1) {
		callback([]);
	} else {
		$.ajax('/tools/naamsuggesties.php?vorm=user&zoekin=voorkeur&q=' + encodeURI(term))
			.done((data) => {
				callback(data);
			})
			.fail(() => {
				callback([]);
			});
	}
}

function template(data: any, term: string) {
	return data.value;
}
