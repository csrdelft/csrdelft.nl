function queryString() {
	this.parameters = new Array();
	
	this.addParameter = function (name, value) {
		this.parameters.push(name + '=' + value);
	}
	
	this.toString = function () { 
		return this.parameters.join('&'); 
	}
}

function showMelding(melding) {
	document.getElementById('melding').innerHTML = melding;
	document.getElementById('melding').style.display = 'block';
}

function hideMelding() {
	document.getElementById('melding').style.display = 'none';
}

function showPopupKop(melding, kop) {
	document.getElementById('dialogContents').innerHTML = melding;
	document.getElementById('dialogKop').innerHTML = kop;
	sm('box',400,90);
}

function showPopup(melding) {
	showMeldingKop(melding, 'Mededeling');
}

function hidePopup() {
	document.getElementById('dialogContents').innerHTML = '';
	hm('box');
}

function showBusyIndicator() {
	document.getElementById('divBusyIndicator').style.display = 'block';
}

function hideBusyIndicator() {
	document.getElementById('divBusyIndicator').style.display = 'none';
}

function busyIndicatorIsVisible() {
	return (document.getElementById('divBusyIndicator').style.display == 'block');
}

function doeNiets() {
	return true;
}

/* doe een verzoek en meld het resultaat, doe bij slagen eerst functionOnSuccess */
function ajaxRequestAndDo(request, functionOnSuccess) {
	var tempVisibility = busyIndicatorIsVisible();
	
	if (!tempVisibility) showBusyIndicator();
	
	hideMelding();
	hidePopup();
	var oXmlHttp = zXmlHttp.createRequest();
	oXmlHttp.open("get", request);
	oXmlHttp.onreadystatechange = function () {
		if (oXmlHttp.readyState == 4) {
			if (oXmlHttp.status == 200) {
				if (functionOnSuccess()) showMelding(oXmlHttp.responseText);
			} else {
				showMelding("Fout opgetreden: " + oXmlHttp.statusText); //statusText is not always accurate
			}
		}            
	};
	
	oXmlHttp.send(null);
	
	if (!tempVisibility) hideBusyIndicator();
}

/* Doe een verzoek en plaats het resultaat in het element met meegegeven id */
function ajaxRequestToId(request, elementId) {
	var tempVisibility = busyIndicatorIsVisible();
	
	if (!tempVisibility) showBusyIndicator();
	
	var oXmlHttp = zXmlHttp.createRequest();
	oXmlHttp.open("get", request);
	oXmlHttp.onreadystatechange = function () {
		if (oXmlHttp.readyState == 4) {
			if (oXmlHttp.status == 200) {
				document.getElementById(elementId).innerHTML = oXmlHttp.responseText;
			} else {
				showMelding("Fout opgetreden: " + oXmlHttp.statusText); //statusText is not always accurate
			}
		}
	};
	
	oXmlHttp.send(null);
	
	if (!tempVisibility) hideBusyIndicator();
}
		
/* Doe een verzoek en retourneer het resultaat */
function ajaxRequestReturnTo(request, elementId) {
	var tempVisibility = busyIndicatorIsVisible();
	
	if (!tempVisibility) showBusyIndicator();
	
	var oXmlHttp = zXmlHttp.createRequest();
	oXmlHttp.open("get", request);
	oXmlHttp.onreadystatechange = function () {
		if (oXmlHttp.readyState == 4) {
			if (oXmlHttp.status == 200) {
				document.getElementById(elementId).innerHTML = oXmlHttp.responseText;
			} else {
				document.getElementById(elementId).innerHTML = 'Fout opgetreden: ' + oXmlHttp.statusText; //statusText is not always accurate
			}
		}
	};
	
	oXmlHttp.send(null);
	
	if (!tempVisibility) hideBusyIndicator();
}

/* doe een verzoek en meld het resultaat, doe bij 'ok' volgende aanroep, anders resultaat in elementIdFail */
function ajaxRequestFormOnOkDo(request, nextFunctionOnSuccess, elementIdFail) {
	var tempVisibility = busyIndicatorIsVisible();
	
	if (!tempVisibility) showBusyIndicator();
	
	hideMelding();
	hidePopup();
	var oXmlHttp = zXmlHttp.createRequest();
	oXmlHttp.open("get", request);
	oXmlHttp.onreadystatechange = function () {
		if (oXmlHttp.readyState == 4) {
			if (oXmlHttp.status == 200) {
				if (oXmlHttp.responseText == 'ok') {
					nextFunctionOnSuccess();
				} else {
					document.getElementById(elementIdFail).innerHTML = oXmlHttp.responseText;
					showMelding('Fout: formulier niet goed ingevuld.');
				}
			} else {
				showMelding("Fout opgetreden bij controle: " + oXmlHttp.statusText); //statusText is not always accurate
			}
			
			oForm.reset();
		}            
	};
	
	oXmlHttp.send(null);
	
	if (!tempVisibility) hideBusyIndicator();
}

/* doe een verzoek en doe bij 'ok' volgende aanroep, meld anders het resultaat */
function ajaxRequestOnOkDo(request, nextFunctionOnSuccess) {
	var tempVisibility = busyIndicatorIsVisible();
	
	if (!tempVisibility) showBusyIndicator();
	
	hideMelding();
	hidePopup();
	var oXmlHttp = zXmlHttp.createRequest();
	oXmlHttp.open("get", request);
	oXmlHttp.onreadystatechange = function () {
		if (oXmlHttp.readyState == 4) {
			if (oXmlHttp.status == 200) {
				if (oXmlHttp.responseText == 'ok') {
					nextFunctionOnSuccess();
				} else {
					showMelding(oXmlHttp.responseText);
				}
			} else {
				showMelding("Fout opgetreden bij controle: " + oXmlHttp.statusText); //statusText is not always accurate
			}
			
			oForm.reset();
		}            
	};
	
	oXmlHttp.send(null);
	
	if (!tempVisibility) hideBusyIndicator();
}

/* doe een verzoek en doe bij 'ok' volgende aanroep, meld anders het resultaat */
function ajaxPostRequestOnOkDo(request, nextFunctionOnSuccess, qString) {
	var tempVisibility = busyIndicatorIsVisible();
	
	if (!tempVisibility) showBusyIndicator();
	
	hideMelding();
	hidePopup();
	var oXmlHttp = zXmlHttp.createRequest();
	oXmlHttp.open("post", request, true);
	oXmlHttp.onreadystatechange = function () {
		if (oXmlHttp.readyState == 4) {
			if (oXmlHttp.status == 200) {
				if (oXmlHttp.responseText == 'ok') {
					nextFunctionOnSuccess();
				} else {
					//document.getElementById('divBeschrijvingen').innerHTML = oXmlHttp.responseText;
					showMelding(oXmlHttp.responseText);
				}
			} else {
				showMelding("Fout opgetreden bij controle: " + oXmlHttp.statusText); //statusText is not always accurate
			}
			
			oForm.reset();
		}            
	};
	
	oXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
	oXmlHttp.send(qString);
	
	if (!tempVisibility) hideBusyIndicator();
}
