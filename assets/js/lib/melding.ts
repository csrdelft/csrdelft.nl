import { selectAll } from './dom';
import { slideUp } from './animatie';

export function initSluitMeldingen(): void {
	selectAll('#melding').forEach((el) => {
		el.addEventListener('click', (event) => {
			if (event.target instanceof HTMLElement) {
				const alert = event.target.closest('.alert') as HTMLElement;
				if (event.target.contains(alert)) {
					slideUp(alert);
				}
			}
		});
	});
}
