import {Calendar, Duration, EventApi, View} from '@fullcalendar/core';
// @ts-ignore
import nlLocale from '@fullcalendar/core/locales/nl';
import {OptionsInput, ToolbarInput} from '@fullcalendar/core/types/input-types';
import dayGridPlugin from '@fullcalendar/daygrid';
import interaction from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';
import axios from 'axios';
import $ from 'jquery';
import moment from 'moment';
import Popper from 'popper.js';
import {ajaxRequest} from './ajax';
import {domUpdate} from './context';
import ctx from './ctx';
import {htmlParse} from './util';

const dateTimeFormat = 'YYYY-MM-DD HH:mm:ss';

const fmt = (date: Date) => moment(date).format(dateTimeFormat);

const calendarEl = document.getElementById('agenda');

if (calendarEl == null) {
	throw new Error('Agenda element niet gevonden');
}

const {jaar, maand, weergave, creator} = calendarEl.dataset;

if (jaar == null || maand == null || weergave == null || creator == null) {
	throw new Error('Agenda opties niet gezet');
}

const defaultView = {
	maand: 'dayGridMonth',
	week: 'timeGridWeek',
	dag: 'timeGridDay',
	agenda: 'listMonth',
}[weergave];

const options: OptionsInput = {
	plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interaction],
	height: 'auto',
	nowIndicator: true,
	defaultView,
	locale: nlLocale,
	customButtons: {
		nieuw: {
			text: 'Nieuw',
			click: () => {
				const datum = fmt(calendar.getDate());
				ajaxRequest('POST', '/agenda/toevoegen', {
					begin_moment: datum,
					eind_moment: datum,
				}, false, domUpdate);
			},
		},
	},
	header: {
		left: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
		center: 'title',
		right: 'today prevYear,prev,next,nextYear',
	},
	defaultDate: new Date(Number(jaar), Number(maand) - 1),
	firstDay: 0,
	events: '/agenda/feed',
	selectable: creator === 'true',
	select: (selectionInfo) => {
		ajaxRequest('POST', '/agenda/toevoegen', {
			begin_moment: fmt(selectionInfo.start),
			eind_moment: fmt(selectionInfo.end),
		}, false, domUpdate);
	},
	eventClick: (info) => {
		axios.get(`/agenda/details/${info.event.id}`).then((response) => {
			const card = htmlParse(response.data)[0] as HTMLElement;
			card.style.zIndex = '100';
			card.style.position = 'absolute';

			card.querySelector('.close')!.addEventListener('click', () => {
				card.remove();
				return false;
			});

			document.body.append(card);
			ctx.init(card);

			// tslint:disable-next-line:no-unused-expression
			new Popper(info.el, card, {placement: 'bottom'});

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
	eventDrop(dropInfo) {
		axios.post(`/agenda/verplaatsen/${dropInfo.event.id}`, {
			begin_moment: fmt(dropInfo.event.start!),
			eind_moment: fmt(dropInfo.event.end!),
		}).then(() => calendar.refetchEvents());
	},
	eventResize(resizeInfo) {
		axios.post(`/agenda/verplaatsen/${resizeInfo.event.id}`, {
			begin_moment: fmt(resizeInfo.event.start!),
			eind_moment: fmt(resizeInfo.event.end!),
		}).then(() => calendar.refetchEvents());
	},
};

// Creator krijgt nieuw knop
if (creator === 'true') {
	const header = options.header as ToolbarInput;
	header.right = 'nieuw ' + header.right;
}

const calendar = new Calendar(calendarEl, options);
calendar.render();

ctx.addHandler('.ReloadAgenda',
	(el) => el.addEventListener('click', () => setTimeout(() => calendar.refetchEvents())));

$(document.body).on('modalClose', () => calendar.refetchEvents());
