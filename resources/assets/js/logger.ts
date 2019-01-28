import axios from 'axios';

window.addEventListener('error', (ev) => {
	if (ev.message.toLowerCase().indexOf('script error') > -1) {
		axios.post('/logger', {
			message: ev.message,
			pagina: window.location.href,
			url: ev.filename,
		});
	} else {
		axios.post('/logger', {
			col: ev.colno,
			error: ev.error.stack,
			line: ev.lineno,
			message: ev.message,
			pagina: window.location.href,
			url: ev.filename,
		});
	}

	return false;
});
