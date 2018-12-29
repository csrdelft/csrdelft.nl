// Laad styles
import '../../sass/effect/trein.scss';

class TjoekTjoek {
	treinen = [
		'ns-ddz-4',
		'ns-ddz-6',
		'ns-icm-3',
		'ns-icm-4',
		'ns-icr-7',
		'ns-icr-9',
		'flirt3-blauw',
		'arriva',
		'ns-virm-4',
		'ns-virm-6',
		'ns-sgmm-2',
		'ns-sgmm-3',
		'ns-flirt-3',
		'ns-slt-6',
		'ns-sng-4',
		'rnet-gtw',
		'thalys',
		'iceje',
	];

	rails = null;

	get randomTrein() {
		return this.treinen[Math.floor(Math.random() * this.treinen.length)];
	}

	constructor() {
		this.rails = document.querySelector('.rails');
	}

	start() {
		setTimeout(() => {
			this.stuurTrein();
			setInterval(() => {
				this.stuurTrein();
			}, 18000);
		}, Math.random() * 5000 + 5000);
	}

	stuurTrein() {
		let trein = document.createElement('div');
		trein.setAttribute('class', `trein ${this.randomTrein}`);
		this.rails.appendChild(trein);

		setTimeout(() => trein.remove(), 13000);
	}
}

new TjoekTjoek().start();
