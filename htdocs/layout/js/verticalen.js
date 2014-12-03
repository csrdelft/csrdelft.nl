var kringen = [];

function toggleEmails(vertkring) {
	if (typeof (kringen[vertkring]) == 'undefined') {
		//request doen voor de tab-inhoud
		http.abort();
		http.open("GET", "/communicatie/verticalen.php?email=" + vertkring, true);
		http.onreadystatechange = function() {
			if (http.readyState == 4) {
				kringen[vertkring] = document.getElementById('leden' + vertkring).innerHTML;
				document.getElementById('leden' + vertkring).innerHTML = http.responseText;
				selectText(document.getElementById('leden' + vertkring));
			}
		};
		http.send(null);
	} else {
		document.getElementById('leden' + vertkring).innerHTML = kringen[vertkring];
		delete kringen[vertkring];
	}
}
