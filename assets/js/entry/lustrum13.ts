const section = document.querySelector<HTMLElement>('[data-puzzle-section]');
const board = section?.querySelector<HTMLElement>('.puzzle-board');
const status = section?.querySelector<HTMLElement>('[data-puzzle-status]');
const reset = section?.querySelector<HTMLButtonElement>('[data-puzzle-reset]');

if (section && board && status && reset) {
	let pieces: number[] = [];
	let selected: number | null = null;
	let moves = 0;
	let complete = false;
	const buttons: HTMLButtonElement[] = [];

	const render = () => {
		complete = pieces.every((piece, position) => piece === position);
		board.classList.toggle('is-complete', complete);
		buttons.forEach((button, position) => {
			const piece = pieces[position];
			button.style.backgroundPosition = `${(piece % 3) * 50}% ${
				Math.floor(piece / 3) * 100
			}%`;
			button.setAttribute('aria-pressed', String(selected === position));
			button.setAttribute(
				'aria-label',
				`Stuk op rij ${Math.floor(position / 3) + 1}, kolom ${
					(position % 3) + 1
				}`
			);
		});
		status.textContent = complete
			? `Het panorama klopt weer! ${moves} verwisselingen.`
			: selected === null
			? `${moves} verwisselingen. Kies een stuk.`
			: 'Kies een tweede stuk om te verwisselen.';
	};

	for (let position = 0; position < 6; position++) {
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'puzzle-piece';
		button.addEventListener('click', () => {
			if (complete) return;
			if (selected === position) selected = null;
			else if (selected === null) selected = position;
			else {
				[pieces[selected], pieces[position]] = [
					pieces[position],
					pieces[selected],
				];
				selected = null;
				moves++;
			}
			render();
		});
		board.append(button);
		buttons.push(button);
	}
	const shuffle = () => {
		pieces = [0, 1, 2, 3, 4, 5];
		for (let i = pieces.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[pieces[i], pieces[j]] = [pieces[j], pieces[i]];
		}
		if (pieces.every((piece, position) => piece === position)) {
			[pieces[0], pieces[1]] = [pieces[1], pieces[0]];
		}
		selected = null;
		moves = 0;
		render();
	};
	reset.addEventListener('click', shuffle);
	shuffle();
	section.hidden = false;
}

export {};
