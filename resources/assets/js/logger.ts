import axios from 'axios';

window.onerror = function (message: string, url, line, col, error) {
	const string = message.toLowerCase();
	const substring = "script error";
	if (string.indexOf(substring) > -1) {
		axios.post('/logger', {
			message: 'Script error'
		});
	} else {
		axios.post('/logger', {
			message,
			url,
			line,
			col,
			pagina: window.location.href,
			error: JSON.stringify(error)
		});
	}

	return false;
};
