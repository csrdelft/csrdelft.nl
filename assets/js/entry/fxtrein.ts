// Laad styles
import '../../scss/effect/trein.scss';
import { select } from '../lib/dom';

/**
 * Tijd voordat een trein van het spoor wordt gehaald.
 */
const TIMEOUT = 13000;

/**
 * Tijd tussen twee treinen.
 */
const INTERVAL = 18000;

/**
 * Tijd voordat de eerste trein komt.
 */
const OFFSET = Math.random() * 5000 + 5000;

class TjoekTjoek {
	get randomTrein() {
		return this.treinen[Math.floor(Math.random() * this.treinen.length)];
	}
	private rails: Element;
	private treinen = [
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

	constructor() {
		this.rails = select('.rails');
	}

	public start() {
		setTimeout(() => {
			this.stuurTrein();
			setInterval(() => {
				this.stuurTrein();
			}, INTERVAL);
		}, OFFSET);
	}

	public stuurTrein() {
		const trein = document.createElement('div');
		trein.setAttribute('class', `trein ${this.randomTrein}`);
		this.rails.appendChild(trein);

		setTimeout(() => trein.remove(), TIMEOUT);
	}
}

new TjoekTjoek().start();
