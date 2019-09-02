import {Calendar} from '@fullcalendar/core';
// @ts-ignore
import nlLocale from '@fullcalendar/core/locales/nl';
import dayGridPlugin from '@fullcalendar/daygrid';
import interaction from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';
import moment from 'moment';
import {ajaxRequest} from './ajax';
import {domUpdate} from './context';

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
	events: '/agenda/feed',
	selectable: true,
	select: (selectionInfo) => {
		ajaxRequest('POST', '/agenda/toevoegen', {
			begin_moment: moment(selectionInfo.start).format('YYYY-MM-DD hh:mm:ss'),
			eind_moment: moment(selectionInfo.end).format('YYYY-MM-DD hh:mm:ss'),
		}, false, domUpdate, alert);
	},
	eventClick: (info) => {
		if (!info.event.url) {
			ajaxRequest('POST', `/agenda/bewerken/${info.event.id.split('@')[0]}`, {}, false, domUpdate, alert);
		}
	},
});
calendar.render();
