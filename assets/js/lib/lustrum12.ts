import { docReady } from './util';
import { select, selectAll } from './dom';

docReady(() => {
	selectAll('.dies-activiteit-ketzers').forEach((el) => {
		const text1 = 'Laat ketzers zien';
		const text2 = 'Verberg ketzers';
		const toggler = select('.toggler', el);
		const content = select('.ketzers', el);
		toggler.addEventListener('click', () => {
			toggler.innerText = toggler.innerText == text2 ? text1 : text2;
			content.classList.toggle('verborgen');
		});
	});
});
