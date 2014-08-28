
$(document).ready(function() {

	$.backstretch('http://plaetjes.csrdelft.nl/layout2/bg-image-16.jpg');

	var first = true;
	var delayed = false;
	var learnmode = document.title.indexOf('oefenen') >= 0;

	var flip1 = false;
	var flip2 = false;

	$('.memorycard').click(function() {

		flipback(); // gebruiker hoeft niet te wachten op delayed flipback

		if (first) { // start de tijd
			first = false;
			starttijd = new Date();
			update_title();
		}

		if ($(this).hasClass('goed')) { // goed?
			// ignore
		}
		else if (learnmode) { // faden?

			if (flip1 && flip2) {
				alert('reset failed');
			}
			else if (flip1) { // dit is de tweede

				if (($(this).hasClass('naam') && flip1.hasClass('pasfoto')) || ($(this).hasClass('pasfoto') && flip1.hasClass('naam'))) {
					flip2 = $(this);
					if (flip2.hasClass('pasfoto')) {
						$('.memorycard.pasfoto').not(flip2).fadeTo('fast', 0.5);
					}
					else if (flip2.hasClass('naam')) {
						$('.memorycard.naam').not(flip2).fadeTo('fast', 0.5);
					}
					else {
						alert('error');
					}

					check_correctness();
				}
				else {
					// ignore
				}

			}
			else { // dit is de eerste

				flip1 = $(this);
				if (flip1.hasClass('pasfoto')) {
					$('.memorycard.pasfoto').not(flip1).fadeTo('slow', 0.3);
				}
				else if (flip1.hasClass('naam')) {
					$('.memorycard.naam').not(flip1).fadeTo('slow', 0.3);
				}
				else {
					alert('error');
				}

			}

		}
		else { // omdraaien?

			if ($(this).hasClass('flipped')) {

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
			else {

				if (flip1 && flip2) {
					alert('reset failed');
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
			goed += 1;
		}

		delayed = true;
		window.setTimeout(flipback, 1000);
	}

	function flipback() {
		if (delayed) {
			delayed = false;
			if (learnmode) {
				$('.memorycard').fadeTo('slow', 1.0);
				if (flip1.hasClass('goed') && flip2.hasClass('goed')) {
					flip1.removeClass('flipped');
					flip2.removeClass('flipped');
				}
				else {
					$('.memorycard[uid=' + flip1.attr('uid') + ']').not(flip1).effect('shake');
				}
			}
			else {
				if (flip1.hasClass('goed') && flip2.hasClass('goed')) {
					flip1.fadeTo('slow', 0.5);
					flip2.fadeTo('slow', 0.5);
				}
				else {
					flip1.removeClass('flipped');
					flip2.removeClass('flipped');
				}
			}
			flip1 = false;
			flip2 = false;
		}
	}


	function update_title() {

		var nu = new Date();
		var seconds = (nu - starttijd) / 1000;
		var minutes = parseInt(seconds / 60);
		seconds = parseInt(seconds % 60);

		if (seconds < 10) {
			seconds = '0' + seconds;
		}
		if (minutes < 10) {
			minutes = '0' + minutes;
		}
		document.title = goed + '/' + beurten + ' (' + minutes + ':' + seconds + ')';

		if ($('.memorycard').length === $('.memorycard.goed').length) { // stop de tijd
			alert('Gefeliciteerd!\n\n' + document.title);
		}
		else {
			window.setTimeout(update_title, 1000);
		}
	}

});
