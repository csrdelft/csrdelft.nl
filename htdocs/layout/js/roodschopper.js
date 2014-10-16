function roodschopper(actie) {
	http.abort();

	var form = document.getElementById('roodschopper');
	var params = [];
	params.push('actie=' + encodeURIComponent(actie));

	for (var i = 0; i < form.elements.length; i++) {
		if (form.elements[i].type == 'select-one' || form.elements[i].type == 'text' || form.elements[i].type == 'textarea') {
			params.push(form.elements[i].name + '=' + encodeURIComponent(form.elements[i].value));
			form.elements[i].disabled = true;
		}
	}

	http.open('POST', '/tools/roodschopper.php', true);
	http.setRequestHeader('Content-length', params.length);
	http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	http.setRequestHeader('Connection', 'close');

	http.onreadystatechange = function() {
        var div;
        if (http.readyState == 4) {
            if (actie == 'verzenden') {
                location.href = '/tools/roodschopper.php';
            } else {
                div = document.getElementById('messageContainer');
                div.innerHTML = http.responseText;
                $(div).show();
                $('#submitContainer').hide();
            }

        }
	};
	http.send(params.join('&'));
}
function restoreRoodschopper() {
	var form = document.getElementById('roodschopper');
	for (var i = 0; i < form.elements.length; i++) {
		if (form.elements[i].type == 'select-one' || form.elements[i].type == 'text' || form.elements[i].type == 'textarea') {
			form.elements[i].disabled = false;
		}
	}
	$('#submitContainer').show();
	$('#messageContainer').hide();
}
