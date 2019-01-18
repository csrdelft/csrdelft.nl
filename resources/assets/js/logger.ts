import axios from 'axios';

window.onerror = (message: string, url, line, col, error) => {
	const substring = 'script error';
	if (message.toLowerCase().indexOf(substring) > -1) {
		axios.post('/logger', {
			message: 'Error uit extern bestand, geen informatie beschikbaar.',
		});
	} else {
		// tslint:disable-next-line:no-console
		message += '\n' + console.trace();
		axios.post('/logger', {
			col,
			error: JSON.stringify(error),
			line,
			message,
			pagina: window.location.href,
			url,
		});
	}

	return false;
};
