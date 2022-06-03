import $ from 'jquery';
import 'jquery-ui/ui/effect.js';
import 'jquery-ui/ui/effects/effect-shake.js';
import 'jquery-ui/ui/widgets/dialog';

import '../../scss/ledenmemory.scss';

$(() => {
	let first = true;
	let delayed = false;
	const learnmode = document.title.indexOf('oefenen') >= 0;
	let finished = false;
	let flip1: JQuery | null;
	let flip2: JQuery | null;
	let beurten = 0;
	let goed = 0;
	let starttijd: Date;

	function showReel() {
		if (!flip1 || !flip2) {
			return;
		}
		let content = '<div class="box"><table><tbody><tr><td>';

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

		const box = $(content).appendTo('body');

		box.animate(
			{
				left: '60%',
			},
			'slow',
			function () {
				$(this).css('left', '60%');
			}
		);

		$(box)
			.delay(1200)
			.animate(
				{
					left: '-50%',
				},
				'slow',
				() => {
					$(box).remove();
				}
			);
	}

	function flipback() {
		if (!flip1 || !flip2) {
			return;
		}

		if (delayed) {
			delayed = false;
			if (learnmode) {
				$('.memorycard').fadeTo('slow', 1.0);
				if (flip1.hasClass('goed') && flip2.hasClass('goed')) {
					flip1.removeClass('flipped');
					flip2.removeClass('flipped');
				} else {
					$('.memorycard[uid=' + flip1.attr('uid') + ']')
						.not(flip1)
						.effect('shake');
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
			flip1 = null;
			flip2 = null;
		}
	}

	function checkCorrectness() {
		if (!flip1 || !flip2) {
			return;
		}
		beurten += 1;

		if (flip1.attr('uid') === flip2.attr('uid')) {
			// goed
			flip1.addClass('goed');
			flip2.addClass('goed');
			goed += 1;
			showReel();
		}

		const memorycard = $('.memorycard');
		const memorycardGoed = $('.memorycard.goed');

		if (memorycard.length === memorycardGoed.length) {
			// einde: toon alles
			finished = true;
			memorycard.addClass('flipped').fadeTo('fast', 0.5);
		} else {
			delayed = true;
			window.setTimeout(flipback, 1000);
		}
	}

	function updateTitle() {
		const nu = new Date();
		let seconds = Math.floor((nu.getTime() - starttijd.getTime()) / 1000);
		const minutes = Math.floor(seconds / 60);

		seconds = seconds % 60;

		document.title = goed + '/' + beurten + ' (' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds + ')';

		if (!finished) {
			return setTimeout(updateTitle, 1000);
		}
		// einde: stop de tijd

		const dialog = {
			buttons: {},
			height: 334,
			modal: true,
			resizable: false,
			width: 484,
		};
		let content = 'Gefeliciteerd! U heeft alle ' + goed + ' namen goed in ' + beurten + ' beurten';
		content += ' en heeft daar in totaal ' + minutes + ' minuten en ' + seconds + ' seconden over gedaan.';

		if (!learnmode) {
			content +=
				'<p>Wilt u deze score toevoegen aan de lijst met hoogste scores?</p>' +
				'<input name="eerlijk" id="eerlijk" type="checkbox" />' +
				'<label for="eerlijk"> Ik heb deze score eerlijk verkregen</label>';

			let eerlijk = false;
			$(document).on('change', '#eerlijk', function () {
				eerlijk = this.checked;
			});

			dialog.buttons = {
				Ja() {
					$.post('/leden/memoryscore/', {
						beurten,
						eerlijk: eerlijk ? 1 : 0,
						goed,
						groep: $('body').data('groep'),
						tijd: minutes * 60 + seconds,
					});
					$(this).dialog('close');
				},
				Nee() {
					$(this).dialog('close');
				},
			};
		}
		$('<div id="dialog-finish" class="blue">' + content + '</div>').appendTo('body');

		$('#dialog-finish').dialog(dialog);
	}

	$('.memorycard').on('click', function () {
		flipback(); // gebruiker hoeft niet te wachten op delayed flipback

		if (first) {
			// start de tijd
			first = false;
			starttijd = new Date();
			updateTitle();
		}

		if ($(this).hasClass('goed')) {
			// goed?
			// ignore
		} else if (learnmode) {
			// faden?

			if (flip1 && flip2) {
				alert('reset failed');
			} else if (flip1) {
				// dit is de tweede
				if (
					($(this).hasClass('naam') && flip1.hasClass('pasfoto')) ||
					($(this).hasClass('pasfoto') && flip1.hasClass('naam'))
				) {
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
			} else {
				// dit is de eerste

				flip1 = $(this);
				if (flip1.hasClass('pasfoto')) {
					$('.memorycard.pasfoto').not(flip1).fadeTo('slow', 0.3);
				} else if (flip1.hasClass('naam')) {
					$('.memorycard.naam').not(flip1).fadeTo('slow', 0.3);
				} else {
					alert('error');
				}
			}
		} else {
			// omdraaien?

			if ($(this).hasClass('flipped')) {
				if (flip1 && flip1.get(0) === $(this).get(0)) {
					// ignore
				} else if (flip2 && flip2.get(0) === $(this).get(0)) {
					// ignore
				} else {
					alert('flipback failed');
				}
			} else {
				if (flip1 && flip2) {
					alert('reset failed');
				} else if (flip1) {
					// dit is de tweede

					if (
						($(this).hasClass('naam') && flip1.hasClass('pasfoto')) ||
						($(this).hasClass('pasfoto') && flip1.hasClass('naam'))
					) {
						flip2 = $(this);
						flip2.addClass('flipped');

						checkCorrectness();
					} else {
						// ignore
					}
				} else {
					// dit is de eerste
					flip1 = $(this);
					flip1.addClass('flipped');
				}
			}
		}
	});
});
