
$(document).ready(function () {
	var first = true;
	var delayed = false;
	var learnmode = document.title.indexOf('oefenen') >= 0;
	var finished = false;
	var flip1 = false;
	var flip2 = false;

	var beurten = 0;
	var goed = 0;
	var starttijd;

	function showReel() {
		var content = '<div class="box"><table><tbody><tr><td>';
		if (flip1.hasClass('pasfoto')) {
			content += flip1.find('img').parent().html();
			content += '</td><td><h3>' + flip2.find('h3').attr('title') + '</h3>';
		} else if (flip2.hasClass('pasfoto')) {
			content += flip2.find('img').parent().html();
			content += '</td><td><h3>' + flip1.find('h3').attr('title') + '</h3>';
		} else {
			alert('error');
		}
		content += '</td></tr></tbody></table></div>';
		var box = $(content).appendTo('body');
		box.animate({
			left: '60%'
		}, 'slow', function () {
			$(this).css('left', '60%');
		});

		$(box).delay(1200).animate({
			left: '-50%'
		}, 'slow', function () {
			$(box).remove();
		});
	}

	function checkCorrectness() {
		beurten += 1;

		if (flip1.attr('uid') === flip2.attr('uid')) { // goed
			flip1.addClass('goed');
			flip2.addClass('goed');
			goed += 1;
			showReel();
		}

		if ($('.memorycard').length === $('.memorycard.goed').length) { // einde: toon alles
			finished = true;
			$('.memorycard').addClass('flipped').fadeTo('fast', 0.5);
		} else {
			delayed = true;
			window.setTimeout(flipback, 1000);
		}

	}

	function flipback() {
		if (delayed) {
			delayed = false;
			if (learnmode) {
				$('.memorycard').fadeTo('slow', 1.0);
				if (flip1.hasClass('goed') && flip2.hasClass('goed')) {
					flip1.removeClass('flipped');
					flip2.removeClass('flipped');
				} else {
					$('.memorycard[uid=' + flip1.attr('uid') + ']').not(flip1).effect('shake');
				}
			} else {
				if (flip1.hasClass('goed') && flip2.hasClass('goed')) {
					flip1.fadeTo('slow', 0.5);
					flip2.fadeTo('slow', 0.5);
				} else {
					flip1.removeClass('flipped');
					flip2.removeClass('flipped');
				}
			}
			flip1 = false;
			flip2 = false;
		}
	}

	function updateTitle() {

		var nu = new Date();
		var seconds = (nu - starttijd) / 1000;
		var minutes = parseInt(seconds / 60);
		seconds = parseInt(seconds % 60);

		document.title = goed + '/' + beurten + ' (' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds + ')';

		if (!finished) {
			return window.setTimeout(updateTitle, 1000);
		}
		// einde: stop de tijd

		var dialog = {
			modal: true,
			width: 484,
			height: 334,
			resizable: false
		};
		var content = 'Gefeliciteerd! U heeft alle ' + goed + ' namen goed in ' + beurten + ' beurten';
		content += ' en heeft daar in totaal ' + minutes + ' minuten en ' + seconds + ' seconden over gedaan.';

		if (!learnmode) {
			content += '<p>Wilt u deze score toevoegen aan de lijst met hoogste scores?</p>';
			content += '<input name="eerlijk" id="eerlijk" type="checkbox" /><label for="eerlijk"> Ik heb deze score eerlijk verkregen</label>';

			var eerlijk = false;
			$(document).on('change', '#eerlijk', function () {
				eerlijk = this.checked;
			});

			dialog['buttons'] = {
				'Ja': function () {
					$.post('/leden/memoryscore/', {
						tijd: minutes * 60 + seconds,
						beurten: beurten,
						goed: goed,
						groep: $('body').data('groep'),
						eerlijk: eerlijk ? 1 : 0
					});
					$(this).dialog('close');
				},
				'Nee': function () {
					$(this).dialog('close');
				}
			};
		}
		$('<div id="dialog-finish" class="blue">' + content + '</div>').appendTo('body');

		$('#dialog-finish').dialog(dialog);
	}

	$('.memorycard').click(function () {

		flipback(); // gebruiker hoeft niet te wachten op delayed flipback

		if (first) { // start de tijd
			first = false;
			starttijd = new Date();
			updateTitle();
		}

		if ($(this).hasClass('goed')) { // goed?
			// ignore
		} else if (learnmode) { // faden?

			if (flip1 && flip2) {
				alert('reset failed');
			} else if (flip1) { // dit is de tweede

				if (($(this).hasClass('naam') && flip1.hasClass('pasfoto')) || ($(this).hasClass('pasfoto') && flip1.hasClass('naam'))) {
					flip2 = $(this);
					if (flip2.hasClass('pasfoto')) {
						$('.memorycard.pasfoto').not(flip2).fadeTo('fast', 0.5);
					} else if (flip2.hasClass('naam')) {
						$('.memorycard.naam').not(flip2).fadeTo('fast', 0.5);
					} else {
						alert('error');
					}

					checkCorrectness();
				} else {
					// ignore
				}

			} else { // dit is de eerste

				flip1 = $(this);
				if (flip1.hasClass('pasfoto')) {
					$('.memorycard.pasfoto').not(flip1).fadeTo('slow', 0.3);
				} else if (flip1.hasClass('naam')) {
					$('.memorycard.naam').not(flip1).fadeTo('slow', 0.3);
				} else {
					alert('error');
				}

			}

		} else { // omdraaien?

			if ($(this).hasClass('flipped')) {

				if (flip1.get(0) === $(this).get(0)) {
					// ignore
				} else if (flip2.get(0) === $(this).get(0)) {
					// ignore
				} else {
					alert('flipback failed');
				}
			} else {

				if (flip1 && flip2) {
					alert('reset failed');
				} else if (flip1) { // dit is de tweede

					if (($(this).hasClass('naam') && flip1.hasClass('pasfoto')) || ($(this).hasClass('pasfoto') && flip1.hasClass('naam'))) {
						flip2 = $(this);
						flip2.addClass('flipped');

						checkCorrectness();
					} else {
						// ignore
					}

				} else { // dit is de eerste
					flip1 = $(this);
					flip1.addClass('flipped');
				}

			}
		}

	});

});
