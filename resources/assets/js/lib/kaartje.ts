import axios from 'axios';
import Popper from 'popper.js';

const kaartjes = {};

export const initKaartjes = (el: HTMLElement): void => {
	const uid = el.dataset.visite;

	if (!uid) {
		throw new Error("data-visite niet gezet op link")
	}

	if (!(uid in kaartjes)) {
		kaartjes[uid] = document.createElement('div');
		kaartjes[uid].style.zIndex = '1000';
	}
	let loading = false;
	let loaded = false;
	el.addEventListener('mouseenter', async () => {
		if (loading) {
			return;
		}

		el.append(kaartjes[uid]);
		new Popper(el, kaartjes[uid], {placement: 'bottom-start'});

		loading = true;
		if (!loaded) {
			const kaartje = await axios.get(`/profiel/${el.dataset.visite}/kaartje`);
			kaartjes[uid].innerHTML = kaartje.data;
			loaded = true;
		}
		loading = false;
	});
	el.addEventListener('mouseleave', () => {
		kaartjes[uid].remove();
	});
};
