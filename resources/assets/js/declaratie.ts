import {docReady} from './util';

const refresh = () => {
	document.querySelectorAll('.TableField-DeleteRow').forEach((el) => {
		el.addEventListener('click', () => {
			el.closest('tr')!.remove();
		});
	});

	const addBtn = document.querySelector('.TableField-AddRow')!;
	const tableField = addBtn.closest('.TableField')!;
	addBtn.addEventListener('click', () => {
		const tbody = tableField.querySelector('tbody')!;
		tbody.append(tbody.querySelector('tr')!.cloneNode(true));
	});
};

docReady(() => {
	console.log('declaratie!');
	// refresh();
});
