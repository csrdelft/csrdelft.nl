import { Calendar } from '@fullcalendar/core';
import nlLocale from '@fullcalendar/core/locales/nl';
import dayGridPlugin from '@fullcalendar/daygrid';
import interaction from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';
import axios from 'axios';
import moment from 'moment';
import ctx from '../ctx';
import { ajaxRequest } from '../lib/ajax';
import { domUpdate } from '../lib/domUpdate';
import { docReady, htmlParse } from '../lib/util';
import { createPopper } from '@popperjs/core';

docReady(() => {
	const dateTimeFormat = 'YYYY-MM-DD HH:mm:ss';

	const fmt = (date: Date) => moment(date).format(dateTimeFormat);

	const calendarEl = document.getElementById('agenda');

	if (calendarEl == null) {
		throw new Error('Agenda element niet gevonden');
	}

	const { jaar, maand, weergave, creator } = calendarEl.dataset;

	if (jaar == null || maand == null || weergave == null || creator == null) {
		throw new Error('Agenda opties niet gezet');
	}

	const initialView = {
		maand: 'dayGridMonth',
		week: 'timeGridWeek',
		dag: 'timeGridDay',
		agenda: 'listMonth',
	}[weergave];

	let editable = false;

	const options = {
		plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interaction],
		height: 'auto',
		nowIndicator: true,
		initialView,
		locale: nlLocale,
		customButtons: {
			nieuw: {
				// Alleen zichtbaar als je mag bewerken
				text: 'Nieuw',
				click: () => {
					const datum = fmt(calendar.getDate());
					ajaxRequest(
						'POST',
						'/agenda/toevoegen',
						{
							begin_moment: datum,
							eind_moment: datum,
						},
						null,
						domUpdate
					);
				},
			},
			bewerken: {
				// Alleen zichtbaar als je mag bewerken
				text: 'Bewerken',
				click() {
					editable = !editable;

					calendar.setOption('editable', editable);
					calendar.setOption('selectable', editable);

					calendar.refetchEvents();

					// De button wordt ververst door fullcalendar, zorg ervoor dat de laatste wordt gepakt.
					setTimeout(() => {
						const button = calendarEl.querySelector('.fc-bewerken-button');

						if (!button) {
							throw new Error('Geen bewerken knop gevonden');
						}

						button.classList.toggle('fc-button-active', editable);
					});
				},
			},
		},
		eventDataTransform: (event) => {
			if (event.editable === true) {
				event.editable = editable;
			}

			return event;
		},
		headerToolbar: {
			left: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
			center: 'title',
			right: 'today prevYear,prev,next,nextYear',
		},
		initialDate: new Date(Number(jaar), Number(maand) - 1),
		firstDay: 0,
		events: '/agenda/feed',
		selectable: editable && creator === 'true',
		select: (selectionInfo) => {
			ajaxRequest(
				'POST',
				'/agenda/toevoegen',
				{
					begin_moment: fmt(selectionInfo.start),
					eind_moment: fmt(selectionInfo.end),
				},
				null,
				domUpdate
			);
		},
		eventClick: (info) => {
			const start = info.event.start;

			if (!start) {
				return;
			}

			axios.get(`/agenda/details/${info.event.id}?jaar=${start.getFullYear()}`).then((response) => {
				const card = htmlParse(response.data)[0] as HTMLElement;
				card.style.zIndex = '100';
				card.style.position = 'absolute';

				const closeButton = card.querySelector('.close');

				if (closeButton) {
					closeButton.addEventListener('click', () => {
						card.remove();
						return false;
					});
				}

				document.body.append(card);
				ctx.init(card);

				createPopper(info.el, card, { placement: 'bottom' });

				// Na deze klik een event listener
				setTimeout(() => {
					const clickListener = (e: Event) => {
						if (!card.contains(e.target as Node)) {
							card.remove();
							document.body.removeEventListener('click', clickListener);
						}
					};

					document.body.addEventListener('click', clickListener);
				});
			});
		},
		eventDrop: async (dropInfo) => {
			const start = dropInfo.event.start;
			const end = dropInfo.event.end;

			if (!start || !end) {
				throw new Error('Drop heeft geen start of end');
			}

			await axios.post(`/agenda/verplaatsen/${dropInfo.event.id}`, {
				begin_moment: fmt(start),
				eind_moment: fmt(end),
			});

			calendar.refetchEvents();
		},
		eventResize: async (resizeInfo) => {
			const start = resizeInfo.event.start;
			const end = resizeInfo.event.end;

			if (!start || !end) {
				throw new Error('Resize heeft geen start of end');
			}

			await axios.post(`/agenda/verplaatsen/${resizeInfo.event.id}`, {
				begin_moment: fmt(start),
				eind_moment: fmt(end),
			});

			calendar.refetchEvents();
		},
	};

	ctx.addHandler('.ReloadAgenda', (el: Element) =>
		el.addEventListener('click', () => setTimeout(() => calendar.refetchEvents()))
	);

	// Creator krijgt nieuw knoppen
	if (creator === 'true') {
		const header = options.headerToolbar;
		header.right = 'bewerken,nieuw ' + header.right;
	}

	const calendar = new Calendar(calendarEl, options);
	calendar.render();

	document.addEventListener('modalClose', () => calendar.refetchEvents());
});
