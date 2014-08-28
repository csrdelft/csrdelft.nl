
$(document).ready(function() {

	$.backstretch('http://plaetjes.csrdelft.nl/layout2/bg-image-16.jpg');

	var first = true;
	var delayed = false;

	var flip1 = false;
	var flip2 = false;

	$('.memorycard').click(function() {

		flipback(); // gebruiker hoeft niet te wachten op delayed flipback

		if (first) { // start de tijd
			first = false;
			starttijd = new Date();
			window.setTimeout(update_title, 1000);
		}

		if ($(this).hasClass('goed')) { // goed?
			// ignore
		}
		else if ($(this).hasClass('flipped')) { // terugdraaien?

			if (flip1.get(0) === $(this).get(0)) {
				// ignore
			}
			else if (flip2.get(0) === $(this).get(0)) {
				// ignore
			}
			else {
				alert('flipback failed');
			}

		}
		else { // omdraaien?

			if (flip1 && flip2) { // het moet fout zijn geweest

				flip1.removeClass('flipped');
				flip2.removeClass('flipped');
				flip1 = $(this);
				flip2 = false;
				flip1.addClass('flipped');

			}
			else if (flip1) { // dit is de tweede

				if (($(this).hasClass('naam') && flip1.hasClass('pasfoto')) || ($(this).hasClass('pasfoto') && flip1.hasClass('naam'))) {
					flip2 = $(this);
					flip2.addClass('flipped');

					check_correctness();
				}
				else {
					// ignore
				}

			}
			else { // dit is de eerste
				flip1 = $(this);
				flip1.addClass('flipped');
			}

		}

	});

	var beurten = 0;
	var goed = 0;
	var starttijd;

	function check_correctness() {
		beurten += 1;

		if (flip1.attr('uid') === flip2.attr('uid')) { // goed
			flip1.addClass('goed');
			flip2.addClass('goed');
			flip1.delay(1000).fadeTo('slow', 0.5);
			flip2.delay(1000).fadeTo('slow', 0.5);
			flip1 = false;
			flip2 = false;
			goed += 1;
		}
		else { // fout
			delayed = true;
			window.setTimeout(flipback, 1000);
		}
	}

	function flipback() {
		if (delayed) {
			delayed = false;
			flip1.removeClass('flipped');
			flip2.removeClass('flipped');
			flip1 = false;
			flip2 = false;
		}
	}


	function update_title() {

		var nu = new Date();
		var diff = (nu - starttijd);
		var seconds = diff / 1000;
		var minutes = seconds / 60;
		seconds %= 60;

		document.title = goed + '/' + beurten + ' (' + parseInt(minutes) + ':' + parseInt(seconds) + ')';

		if ($('.memorycard').length === $('.memorycard.goed').length) { // stop de tijd
			alert('Gefeliciteerd!\n\n' + document.title);
		}
		else {
			window.setTimeout(update_title, 1000);
		}
	}

});
