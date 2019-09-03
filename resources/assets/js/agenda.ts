import {Calendar} from '@fullcalendar/core';
// @ts-ignore
import nlLocale from '@fullcalendar/core/locales/nl';
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

const calendarEl = document.getElementById('agenda');

if (calendarEl == null) {
	throw new Error('Agenda element niet gevonden');
}

const calendar = new Calendar(calendarEl, {
	plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interaction],
	defaultView: 'dayGridMonth',
	locale: nlLocale,
	header: {
		left: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
		center: 'title',
		right: 'prevYear,prev,next,nextYear',
	},
	defaultDate: new Date(Number(calendarEl.dataset.jaar), Number(calendarEl.dataset.maand) - 1),
	firstDay: 0,
	events: '/agenda/feed',
	selectable: true,
	select: (selectionInfo) => {
		ajaxRequest('POST', '/agenda/toevoegen', {
			begin_moment: moment(selectionInfo.start).format('YYYY-MM-DD HH:mm:ss'),
			eind_moment: moment(selectionInfo.end).format('YYYY-MM-DD HH:mm:ss'),
		}, false, domUpdate);
	},
	eventClick: (info) => {
		axios.get(`/agenda/details/${info.event.id}`).then((response) => {
			const card = htmlParse(response.data)[0] as HTMLElement;
			card.style.zIndex = '100';
			card.style.position = 'absolute';

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
});
calendar.render();

ctx.addHandler('.ReloadAgenda',
	(el) => el.addEventListener('click', () => setTimeout(() => calendar.refetchEvents())));

$(document.body).on('modalClose', () => calendar.refetchEvents());
