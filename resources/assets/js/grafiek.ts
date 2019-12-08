import axios from 'axios';
import Chart, {ChartData, ChartOptions} from 'chart.js';
import palette from 'google-palette';
import moment from 'moment';
import ctx from './ctx';
import {formatBedrag, html} from './util';

moment.locale('nl');

ctx.addHandlers({
	'.ctx-deelnamegrafiek': initDeelnamegrafiek,
	'.ctx-graph-bar': initBar,
	'.ctx-graph-line': initLine,
	'.ctx-graph-pie': initPie,
	'.ctx-saldografiek': initSaldoGrafiek,
});

function createCanvas(parent: HTMLElement) {
	const canvas = html`<canvas style="width: 100%; height: 100%"/>` as HTMLCanvasElement;
	parent.appendChild(canvas);
	return canvas;
}

function initPie(el: HTMLElement) {
	let data = JSON.parse(el.dataset.data!) as ChartData;

	data = defaultKleuren(data);

	return new Chart(createCanvas(el), {data, type: 'pie'});
}

async function initLine(el: HTMLElement) {
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
		data,
		options: {
			scales: {
				xAxes: [{
					barPercentage: 1.0,
					stacked: true,
					time: {
						tooltipFormat: 'D MMM H:mm ',
					},
					type: 'time',
				}],
				yAxes: [{
					stacked: true,
					ticks: {
						min: 0,
					},
				}],
			},
			tooltips: {
				intersect: false,
				mode: 'index',
			},
		},
		type: 'line',
	});
}

function kleurPerDataset(data: ChartData) {
	const kleuren = palette(['tol', 'qualitative'], data.datasets!.length)[Symbol.iterator]();
	data.datasets!.forEach((dataset) => {
		dataset.pointBorderColor = dataset.backgroundColor = dataset.borderColor = `#${kleuren.next().value}`;
	});
	return data;
}

function defaultKleuren(data: ChartData) {
	data.datasets = data.datasets!
		.map((dataset) => ({
			backgroundColor: palette('tol-rainbow', dataset.data!.length).map((col) => `#${col}`),
			...dataset,
		}));
	return data;
}

function initBar(el: HTMLElement) {
	let data = JSON.parse(el.dataset.data!) as ChartData;
	data = defaultKleuren(data);

	const options: ChartOptions = {
		scales: {
			yAxes: [{
				ticks: {
					beginAtZero: true,
					stepSize: 1,
				},
			}],
		},
	};

	return new Chart(createCanvas(el), {data, type: 'bar', options});
}

function initDeelnamegrafiek(el: HTMLElement) {
	const data = JSON.parse(el.dataset.data!) as any;
	const options: ChartOptions = {
		scales: {
			xAxes: [{
				stacked: true,
				ticks: {
					callback: (t, index) => data.jaren[index],
				},
			}],
			yAxes: [{
				stacked: true,
				ticks: {
					stepSize: 1,
				},
			}],
		},
		tooltips: {
			callbacks: {
				title: (t, d) => d.labels![t[0].index!],
			},
			intersect: false,
			mode: 'index',
		},
	};

	return new Chart(createCanvas(el), {data, type: 'bar', options});
}

Chart.defaults.NegativeTransparentLine = Chart.helpers.clone(Chart.defaults.line);
Chart.controllers.NegativeTransparentLine = Chart.controllers.line.extend({
	update() {
		if (this.chart.data.datasets.length) {
			// get the min and max values
			const min = this.chart.data.datasets[0].data
				.reduce((mininum: number, p: any) => p.y < mininum ? p.y : mininum, this.chart.data.datasets[0].data[0].y);

			if (min >= 0) {
				this.chart.data.datasets[0].borderColor = 'green';
				return Chart.controllers.line.prototype.update.apply(this, arguments);
			}

			const max = this.chart.data.datasets[0].data
				.reduce((maximum: number, p: any) => p.y > maximum ? p.y : maximum, this.chart.data.datasets[0].data[0].y);

			if (max <= 0) {
				this.chart.data.datasets[0].borderColor = 'red';
				return Chart.controllers.line.prototype.update.apply(this, arguments);
			}

			const yScale = this.getScaleForId(this.getMeta().yAxisID);

			// figure out the pixels for these and the value 0
			const top = yScale.getPixelForValue(max);
			const zero = yScale.getPixelForValue(0);
			const bottom = yScale.getPixelForValue(min);

			// build a gradient that switches color at the 0 point
			const context = this.chart.chart.ctx;
			const gradient = context.createLinearGradient(0, top, 0, bottom);
			const ratio = Math.min((zero - top) / (bottom - top), 1);
			gradient.addColorStop(0, 'green');
			gradient.addColorStop(ratio, 'green');
			gradient.addColorStop(ratio, 'red');
			gradient.addColorStop(1, 'red');
			this.chart.data.datasets[0].borderColor = gradient;
		}

		// noinspection JSPotentiallyInvalidConstructorUsage
		return Chart.controllers.line.prototype.update.apply(this, arguments);
	},
});

function initSaldoGrafiek(el: HTMLElement) {
	const closed = el.dataset.closed === 'true';
	const uid = el.dataset.uid!;
	let timespan = 11;

	const options: ChartOptions = {
		scales: {
			xAxes: [{
				time: {
					tooltipFormat: 'LLL',
				},
				type: 'time',
			}],
			yAxes: [{
				ticks: {
					callback: formatBedrag,
				},
			}],
		},
		tooltips: {
			callbacks: {
				label(tooltipItem, data) {
					const datasetLabel = data.datasets![tooltipItem.datasetIndex!].label || '';
					return datasetLabel + ': ' + formatBedrag(Number(tooltipItem.yLabel));
				},
			},
		},
	};

	const chart = new Chart(createCanvas(el), {data: {}, type: 'NegativeTransparentLine', options});

	function load() {
		axios.post(`/profiel/${uid}/saldo/${timespan}`)
			.then((response) => {
				chart.data = response.data;
				chart.update();
			});

		if (!el.querySelector('.saldo-terug-button')) {
			const terugButton = html`<a title="verder terug in de tijd" class="saldo-terug-button">&laquo;</a>`;

			el.appendChild(terugButton);

			terugButton.addEventListener('click', () => {
				timespan = timespan * 2;
				if (timespan > (15 * 356)) {
					return;
				}

				load();
			});
		}
	}

	if (closed) {
		const button = html`<a href="#" class="btn btn-primary">Toon saldografiek</a>`;

		button.addEventListener('click', (e) => {
			e.preventDefault();
			el.classList.remove('verborgen');
			button.remove();
			load();
		});

		el.parentElement!.appendChild(button);
	} else {
		el.classList.remove('verborgen');
		load();
	}
}
