/**
 * Spul voor csrdelft.nl-groepenketzer.
 */

$(document).ready(function() {
	init_groepen();
});

function init_groepen() {
	$('.inline_edit').click(function() {
		//show edit field.
		$(this).children('span').hide();
		$(this).children('input,select').show();
	}).change(function() {
		//id = 'bewerk_<gid>|<uid>'
		var ids = $(this).attr('id').substring(7).split('_');
		var gid = ids[0];
		var uid = ids[1];
		var values = [];
		$(this).children('input,select').each(function(index) {
			values.push($(this).val());
		});
		var data = {'functie[]': values};
		//update span
		$(this).children('span').html(values.join(' - '));

		$.ajax({
			type: 'POST',
			url: '/actueel/groepen/XHR/' + gid + '/bewerkfunctieLid/' + uid,
			data: data,
			cache: false,
			success: function(response) {
				$('.editbox').hide();
				$('.text').show();
			}
		});
	});

	// close editor if clicking outside editfield
	$(document).mouseup(function(object) {
		if (!$(object.target).hasClass('editbox')) { //in editbox mag je klikken
			$('.editbox').hide();
			$('.text').show();
		}
	});

}

function groepFormUpdate() {
	var gAanmeldDiv = document.getElementById('groepAanmeldbaarContainer');
	if (document.getElementById('groepStatus').value == 'ht') {
		$(gAanmeldDiv).show();
		var gLimietDiv = document.getElementById('groepLimietContainer');

		if (document.getElementById('groepAanmeldbaar').value != '') {
			$(gLimietDiv).show();
			//eventueel een opmerking weergeven bij de gekozen optie in de select.
			switch (document.getElementById('toonFuncties').selectedIndex) {
				case 1:
					$('#functieOpmTonenzonderinvoer').show();
					$('#functieOpmVerbergen').hide();
					$('#functieOpmNiet').hide();
					break;
				case 2:
					$('#functieOpmVerbergen').show();
					$('#functieOpmNiet').hide();
					$('#functieOpmTonenzonderinvoer').hide();
					break;
				case 3:
					$('#functieOpmNiet').show();
					$('#functieOpmVerbergen').hide();
					$('#functieOpmTonenzonderinvoer').hide();
					break;
				default:
					$('#functieOpmVerbergen').hide();
					$('#functieOpmNiet').hide();
					$('#functieOpmTonenzonderinvoer').hide();
			}
		} else {
			$(gLimietDiv).hide();
			$('#functieOpmVerbergen').hide();
			$('#functieOpmNiet').hide();
			$('#functieOpmTonenzonderinvoer').hide();
		}
	} else {
		$(gAanmeldDiv).hide();
		var gEindeVeld = document.getElementById('einde');
		if (gEindeVeld.value == '0000-00-00') {
			var now = new Date();
			gEindeVeld.value = now.getFullYear() + '-' + LZ(now.getMonth() + 1) + '-' + LZ(now.getDate());
		}
	}
}

/**
 * @param {Number} x nummer van de maand
 * @return {String} maand, geprefixt met 0 wanneer nodig
 */
function LZ(x) {
	return(x < 0 || x > 9 ? "" : "0") + x
}

/**
 * groepTabShow()
 * tabid is meteen de actie die aangeroepen wordt, een tabje erbij is
 * dus een kwestie van een nieuwe function action_<naam>(){} maken in de
 * controller
 */
function groepTabShow(groepid, tabid) {
	//alle tabjes inactief maken
	var tabs = document.getElementById('tabs').childNodes;
	for (var tabI in tabs) {
		if (tabs[tabI].tagName == 'LI') {
			tabs[tabI].className = '';
		}
	}
	//huidige actief maken.
	document.getElementById(tabid).className = 'active';
	window.location.hash = '#' + tabid;

	//request doen voor de tab-inhoud
	http.abort();
	http.open('GET', '/actueel/groepen/XHR/' + groepid + '/' + tabid, true);
	http.onreadystatechange = function() {
		if (http.readyState == 4) {
			document.getElementById('ledenvangroep' + groepid).innerHTML = http.responseText;
			init_hoverIntents();
			init_groepen();
			if (tabid == 'emails') {
				selectText(document.getElementById('ledenvangroep' + groepid));
			}
		}
	};
	http.send(null);
}