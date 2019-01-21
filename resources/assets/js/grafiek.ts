import c3 from 'c3';
import {format, formatDefaultLocale} from 'd3';
import {timeFormat, timeFormatDefaultLocale} from 'd3-time-format';
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
			const data = JSON.parse(el.dataset.data!);
			c3.generate({
				bindto: el as HTMLElement,
				data: {
					colors: {
						Mannen: '#AFD8F8',
						Vrouwen: '#FFCBDB',
					},
					columns: data,
					type: 'pie',
				},
			});
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
				},
				bindto: el,
				data: {
					columns: data,
					type: 'line',
					x: 'x',
				},
			});
		});
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
			const data = JSON.parse(el.dataset.data!);
			c3.generate({
				bindto: el,
				data: {
					columns: data,
					type: 'bar',
					x: 'x',
				},
			});
		});
}

export function initDeelnamegrafiek(parent: HTMLElement) {
	$(parent).find('.ctx-deelnamegrafiek').each((i, el) => {
		const data = JSON.parse(el.dataset.series!) as any[];

		c3.generate({
			axis: {
				x: {
					tick: {
						format: timeFormat('%Y'),
					},
				},
			},
			bindto: el,
			data: {
				colors: {
					aantalMannen: '#AFD8F8',
					aantalVrouwen: '#FFCBDB',
				},
				groups: [['aantalMannen', 'aantalVrouwen']],
				json: data,
				keys: {
					value: ['aantalMannen', 'aantalVrouwen'],
					x: 'moment',
				},
				names: {
					aantalMannen: 'Mannen',
					aantalVrouwen: 'Vrouwen',
				},
				type: 'bar',
			},
			tooltip: {
				format: {
					title: (moment) => data.find((d) => d.moment === moment).naam,
				},
				grouped: true,
				show: true,
			},
		});
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
