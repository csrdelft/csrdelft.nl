$(document).ready(function() {
	toggleTijden(document.getElementById('field_heledag').checked);
});

function setTijd(a, b, c, d) {
	document.getElementById('field_begin_uur').value = a;
	document.getElementById('field_begin_minuut').value = b;
	document.getElementById('field_eind_uur').value = c;
	document.getElementById('field_eind_minuut').value = d;
}

function toggleTijden(hide) {
	if (hide) {
		document.getElementById('tijden').style.display = 'none';
		document.getElementById('begin').style.display = 'none';
		document.getElementById('eind').style.display = 'none';
	} else {
		document.getElementById('tijden').style.display = 'block';
		document.getElementById('begin').style.display = 'block';
		document.getElementById('eind').style.display = 'block';
	}
}