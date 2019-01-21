/* tslint:disable:no-console */
import axios from 'axios';
import c3, {ChartConfiguration} from 'c3';
import Chart, {ChartData, ChartOptions} from 'chart.js';
import {format, formatDefaultLocale} from 'd3';
import {timeFormat, timeFormatDefaultLocale} from 'd3-time-format';
import palette from 'google-palette';
import $ from 'jquery';

formatDefaultLocale({
	currency: ['€', ''],
	decimal: ',',
	grouping: [3],
	thousands: '.',
});

timeFormatDefaultLocale({
	date: '%d-%m-%Y',
	dateTime: '%a %e %B %Y %T',
	days: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
	months: [
		'januari', 'februari', 'maart', 'april', 'mei', 'juni',
		'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
	periods: ['AM', 'PM'],
	shortDays: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
	shortMonths: ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
	time: '%H:%M:%S',
});

export function initGrafiek(parent: HTMLElement) {
	initDeelnamegrafiek(parent);
	initSaldoGrafiek(parent);
	initPie(parent);
	initLine(parent);
	initBar(parent);
	initLineXY(parent);
}

function createCtx(parent: HTMLElement) {
	const ctx = document.createElement('canvas');
	ctx.style.width = '100%';
	ctx.style.height = '100%';
	parent.append(ctx);
	return ctx;
}

function initPie(parent: HTMLElement | JQuery) {
	if (!(parent instanceof HTMLElement)) {
		parent = parent.get(0);
	}

	if (!parent.querySelectorAll) {
		return;
	}

	parent
		.querySelectorAll('.ctx-graph-pie')
		.forEach((el: HTMLElement) => {
			const ctx = createCtx(el);
			const data = JSON.parse(el.dataset.data!) as ChartData;

			data.datasets = data.datasets!
				.map((dataset) => ({
					backgroundColor: palette('tol', dataset.data!.length).map((col) => `#${col}`),
					...dataset,
				}));

			return new Chart(ctx, {data, type: 'pie'});
		});
}

function initLineXY(parent: HTMLElement | JQuery) {
	if (!(parent instanceof HTMLElement)) {
		parent = parent.get(0);
	}

	if (!parent.querySelectorAll) {
		return;
	}

	parent
		.querySelectorAll('.ctx-graph-line-xy')
		.forEach((el: HTMLElement) => {
			// const ctx = document.createElement('canvas');
			// el.append(ctx);
			// const data = JSON.parse(el.dataset.data!) as ChartData;
			// return new Chart(ctx, {data, type: 'line'});
			const options: ChartConfiguration = {
				axis: {
					x: {
						type: 'timeseries',
					},
				},
				bindto: el,
				data: {
					keys: {
						value: ['y'],
						x: 'x',
					},
					mimeType: 'json',
					type: 'line',
				},
			};

			if (el.dataset.data) {
				options.data.json = JSON.parse(el.dataset.data);
				c3.generate(options);
			} else if (el.dataset.url) {
				axios.post(el.dataset.url).then((response) => {
					options.data.json = response.data;
					c3.generate(options);
				});
			}
		});
}

function initLine(parent: HTMLElement | JQuery) {
	if (!(parent instanceof HTMLElement)) {
		parent = parent.get(0);
	}

	if (!parent.querySelectorAll) {
		return;
	}

	parent
		.querySelectorAll('.ctx-graph-line')
		.forEach((el: HTMLElement) => {
			const data = JSON.parse(el.dataset.data!);

			return new Chart(createCtx(el), {
				data,
				options: {
					scales: {
						xAxes: [{
							type: 'time',
						}],
					},
				},
				type: 'line',
			});
		});
}

function defaultKleuren(data: ChartData) {
	data.datasets = data.datasets!
		.map((dataset) => ({
			backgroundColor: palette('tol', dataset.data!.length).map((col) => `#${col}`),
			...dataset,
		}));
	return data;
}

function initBar(parent: HTMLElement | JQuery) {
	if (!(parent instanceof HTMLElement)) {
		parent = parent.get(0);
	}

	if (!parent.querySelectorAll) {
		return;
	}

	parent
		.querySelectorAll('.ctx-graph-bar')
		.forEach((el: HTMLElement) => {
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

			return new Chart(createCtx(el), {data, type: 'bar', options});
		});
}

export function initDeelnamegrafiek(parent: HTMLElement | JQuery) {
	if (!(parent instanceof HTMLElement)) {
		parent = parent.get(0);
	}

	if (!parent.querySelectorAll) {
		return;
	}

	parent.querySelectorAll('.ctx-deelnamegrafiek').forEach((el: HTMLElement) => {
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

		return new Chart(createCtx(el), {data, type: 'bar', options});
	});
}

export function initSaldoGrafiek(parent: HTMLElement) {
	$(parent).find('.ctx-saldografiek').each((i, el) => {
		const closed = el.dataset.closed === 'true';

		if (closed) {
			const button = document.createElement('a');
			button.setAttribute('href', '#');
			button.setAttribute('class', 'btn btn-primary');
			button.textContent = 'Toon saldografiek';

			button.addEventListener('click', () => {
				el.classList.remove('verborgen');
				button.remove();
			});

			el.parentElement!.append(button);
		} else {
			el.classList.remove('verborgen');
		}

		function gen(t: number) {
			c3.generate({
				axis: {
					x: {
						tick: {
							centered: true,
							count: 10,
							culling: true,
							format: '%x',
						},
						type: 'timeseries',
					},
					y: {
						tick: {
							format: format('($.2f'),
						},
					},
				},
				bindto: el,
				data: {
					keys: {
						value: ['saldo'],
						x: 'moment',
					},
					mimeType: 'json',
					type: 'step',
					url: `/leden/saldo/${el.dataset.uid}/${t}`,
				},
				tooltip: {
					format: {
						title: (x) => timeFormat('%x %X')(x),
					},
				},
			});
		}

		let timespan = 11;
		gen(timespan);

		const terugButton = document.createElement('a');
		terugButton.setAttribute('class', 'saldo-terug-button');
		terugButton.setAttribute('title', 'Verder terug in de tijd...');
		terugButton.innerHTML = '&laquo;';

		el.parentElement!.append(terugButton);

		terugButton.addEventListener('click', () => {
			timespan = timespan * 2;
			if (timespan > (15 * 356)) {
				return;
			}

			gen(timespan);
		});
	});
}
