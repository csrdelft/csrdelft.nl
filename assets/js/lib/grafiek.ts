import axios from 'axios';
import { Chart, ChartData, ChartOptions } from 'chart.js';
import palette from 'google-palette';
import { formatBedrag, html } from './util';

function createCanvas(parent: HTMLElement) {
	const canvas = html`<canvas
		style="width: 100%; height: 100%"
	/>` as HTMLCanvasElement;
	parent.appendChild(canvas);
	return canvas;
}

export function initPie(el: HTMLElement): Chart {
	const stringData = el.dataset.data;

	if (!stringData) {
		throw new Error('initPie: element heeft geen data-data');
	}

	let data = JSON.parse(stringData) as ChartData;

	data = defaultKleuren(data);

	return new Chart(createCanvas(el), { data, type: 'pie' });
}

export async function initLine(el: HTMLElement): Promise<Chart> {
	let data: ChartData;
	if (el.dataset.data) {
		data = JSON.parse(el.dataset.data);
	} else if (el.dataset.url) {
		data = (await axios.post(el.dataset.url)).data;
	} else {
		throw new Error('Hier kan ik niets mee');
	}

	data = kleurPerDataset(data);

	return new Chart(createCanvas(el), {
		type: 'line',
		data,
		options: {
			scales: {
				x: {
					type: 'time',
					time: {
						tooltipFormat: 'D MMM H:mm',
					},
					stacked: true,
				},
				y: {
					stacked: true,
					beginAtZero: true,
				},
			},
			plugins: {
				tooltip: {
					intersect: false,
					mode: 'index',
				},
			},
		},
	});
}

function kleurPerDataset(data: ChartData) {
	const datasets = data.datasets;

	if (!datasets) {
		throw new Error('Data heeft geen datasets');
	}

	const kleuren = palette(['tol', 'qualitative'], datasets.length)[Symbol.iterator]();
	datasets.forEach((dataset) => {
		dataset.pointBorderColor =
			dataset.backgroundColor =
			dataset.borderColor =
				`#${kleuren.next().value}`;
	});
	return data;
}

function defaultKleuren(data: ChartData) {
	const datasets = data.datasets;

	if (!datasets) {
		throw new Error('Data heeft geen datasets');
	}

	data.datasets = datasets.map((dataset) => ({
		backgroundColor: palette('tol-rainbow', dataset.data?.length ?? 0).map(
			(col) => `#${col}`
		),
		...dataset,
	}));
	return data;
}

export function initBar(el: HTMLElement): Chart {
	const stringData = el.dataset.data;

	if (!stringData) {
		throw new Error('initBar: geen data-data attribuut');
	}
	let data = JSON.parse(stringData) as ChartData;
	data = defaultKleuren(data);

	const options: ChartOptions = {
		scales: {
			y: {
				beginAtZero: true,
				ticks: {
					stepSize: 1,
				},
			},
		},
	};

	return new Chart(createCanvas(el), { data, type: 'bar', options });
}

export function initDeelnamegrafiek(el: HTMLElement): Chart {
	const stringData = el.dataset.data;

	if (!stringData) {
		throw new Error('initBar: geen data-data attribuut');
	}
	const data = JSON.parse(stringData) as ChartData & { jaren: number[] };
	const options: ChartOptions = {
		scales: {
			x: {
				stacked: true,
				ticks: {
					callback: (t, index) => data.jaren[index],
				},
			},
			y: {
				stacked: true,
				ticks: {
					stepSize: 1,
				},
			},
		},
		plugins: {
			tooltip: {
				callbacks: {
					title: (t, d) => {
						const labels = d.labels;
						const index = t[0].dataIndex;

						if (!labels || !index) {
							throw new Error('Data heeft geen labels of index');
						}

						return String(labels[index]);
					},
				},
				intersect: false,
				mode: 'index',
			},
		},
	};

	return new Chart(createCanvas(el), { data, type: 'bar', options });
}

function createNegativetransparentLineChartController() {
	if (Chart.defaults.NegativeTransparentLine) {
		return;
	}

	Chart.defaults.NegativeTransparentLine = Chart.defaults.line;
	Chart.controllers.NegativeTransparentLine = Chart.controllers.line.extend({
		update(...args: unknown[]) {
			if (this.chart.data.datasets.length) {
				const dataset = this.chart.data.datasets[0];

				// get the min and max values
				const min = dataset.data.reduce(
					(minimum: number, p: any) => (p.y < minimum ? p.y : minimum),
					dataset.data[0].y
				);

				if (min >= 0) {
					this.chart.data.datasets[0].borderColor = 'green';
					return Chart.controllers.line.prototype.update.apply(this, args);
				}

				const max = dataset.data.reduce(
					(maximum: number, p: any) => (p.y > maximum ? p.y : maximum),
					dataset.data[0].y
				);

				if (max <= 0) {
					this.chart.data.datasets[0].borderColor = 'red';
					return Chart.controllers.line.prototype.update.apply(this, args);
				}

				const yScale = this.getScaleForId(this.getMeta().yAxisID);

				// figure out the pixels for these and the value 0
				const top = yScale.getPixelForValue(max);
				const zero = yScale.getPixelForValue(0);
				const bottom = yScale.getPixelForValue(min);

				// build a gradient that switches color at the 0 point
				const context = this.chart.ctx;
				const gradient = context.createLinearGradient(0, top, 0, bottom);
				const ratio = Math.min((zero - top) / (bottom - top), 1);
				gradient.addColorStop(0, 'green');
				gradient.addColorStop(ratio, 'green');
				gradient.addColorStop(ratio, 'red');
				gradient.addColorStop(1, 'red');
				this.chart.data.datasets[0].borderColor = gradient;
			}

			return Chart.controllers.line.prototype.update.apply(this, args);
		},
	});
}

export function initSaldoGrafiek(el: HTMLElement): void {
	const closed = el.dataset.closed === 'true';
	const uid = el.dataset.uid;

	if (!uid) {
		throw new Error('Saldografiek heeft geen uid');
	}

	let timespan = 11;

	const options: ChartOptions = {
		scales: {
			x: {
				type: 'time',
				time: {
					tooltipFormat: 'LLL',
				},
			},
			y: {
				ticks: {
					callback: formatBedrag,
				},
			},
		},
		plugins: {
			tooltip: {
				callbacks: {
					label(tooltipItem, data) {
						const datasets = data.datasets;
						const datasetIndex = tooltipItem.datasetIndex;

						if (!datasets || datasetIndex === undefined) {
							throw new Error('Saldografiek heeft geen datasets');
						}
						const datasetLabel = datasets[datasetIndex].label || '';
						return datasetLabel + ': ' + formatBedrag(Number(tooltipItem.raw));
					},
				},
			},
		},
	};

	createNegativetransparentLineChartController();

	const chart = new Chart(createCanvas(el), {
		data: {},
		type: 'NegativeTransparentLine',
		options,
	});

	function load() {
		axios.post(`/profiel/${uid}/saldo/${timespan}`).then((response) => {
			chart.data = response.data;
			chart.update();
		});

		if (!el.querySelector('.saldo-terug-button')) {
			const terugButton = html`<a
				title="verder terug in de tijd"
				class="saldo-terug-button"
				>&laquo;</a
			>`;

			el.appendChild(terugButton);

			terugButton.addEventListener('click', () => {
				timespan = timespan * 2;
				if (timespan > 15 * 356) {
					return;
				}

				load();
			});
		}
	}

	if (closed) {
		const button = html`<a href="#" class="btn btn-primary"
			>Toon saldografiek</a
		>`;

		button.addEventListener('click', (e) => {
			e.preventDefault();
			el.classList.remove('verborgen');
			button.remove();
			load();
		});

		const parent = el.parentElement;

		if (!parent) {
			throw new Error('Saldografiek geen onderdeel van DOM');
		}

		parent.appendChild(button);
	} else {
		el.classList.remove('verborgen');
		load();
	}
}
