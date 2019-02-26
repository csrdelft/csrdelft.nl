import axios from 'axios';

window.addEventListener('error', (ev) => {
	axios.post('/logger', {
		col: ev.colno,
		error: ev.error.stack,
		line: ev.lineno,
		message: ev.message,
		pagina: window.location.href,
		url: ev.filename,
	});

	return false;
});
