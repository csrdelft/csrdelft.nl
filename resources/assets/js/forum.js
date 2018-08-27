import $ from 'jquery';
import {CsrBBPreview} from './bbcode';
import {domUpdate} from './context';
import {bbCodeSet} from './bbcode-set';

function toggleForumConceptBtn(enable) {
	let $concept = $('#forumConcept');
	if (typeof enable === 'undefined') {
		$concept.attr('disabled', !$concept.prop('disabled'));
	} else {
		$concept.attr('disabled', !enable);
	}
}

export function saveConceptForumBericht() {
	toggleForumConceptBtn(false);
	let $concept = $('#forumConcept');
	let $textarea = $('#forumBericht');
	let $titel = $('#nieuweTitel');
	if ($textarea.val() !== $textarea.attr('origvalue')) {
		$.post($concept.attr('data-url'), {
			forumBericht: $textarea.val(),
			titel: ($titel.length === 1 ? $titel.val() : ''),
		}).done(function () {
			$textarea.attr('origvalue', $textarea.val());
		}).fail(function (error) {
			alert(error);
		});
	}
	setTimeout(toggleForumConceptBtn, 3000);
}

let bewerkContainer = null;
let bewerkContainerInnerHTML = null;

/**
 * @see inline in forumBewerken
 */
function restorePost() {
	bewerkContainer.html(bewerkContainerInnerHTML);
	$('#bewerk-melding').slideUp(200, function () {
		$(this).remove();
	});
	$('#forumPosten').css('visibility', 'visible');
}

function submitPost(event) {
	event.preventDefault();
	let form = $('#forumEditForm');
	$.ajax({
		type: 'POST',
		cache: false,
		url: form.attr('action'),
		data: form.serialize(),
	}).done((data) => {
		restorePost();
		domUpdate(data);
	}).fail((jqXHR) => alert(jqXHR.responseJSON));
}

/**
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 *
 * @see blade_templates/forum/partial/post_lijst.blade.php
 */
export function forumBewerken(postId) {
	$.ajax({
		url: '/forum/tekst/' + postId,
		method: 'POST',
	}).done((data) => {
		if (document.getElementById('forumEditForm')) {
			restorePost();
		}
		bewerkContainer = $('#post' + postId);
		bewerkContainerInnerHTML = bewerkContainer.html();
		let bewerkForm = `<form id="forumEditForm" class="Formulier" action="/forum/bewerken/${postId}" method="post">` +
			'<div id="bewerkPreview" class="preview forumBericht"></div>' +
			'<textarea name="forumBericht" id="forumBewerkBericht" class="FormElement BBCodeField" rows="8"></textarea>' +
			'Reden van bewerking: <input type="text" name="reden" id="forumBewerkReden"/><br /><br />' +
			'<div class="float-right"><a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a></div>' +
			'<input type="submit" class="opslaan" value="Opslaan" /> ' +
			'<input type="button" class="voorbeeld" value="Voorbeeld" /> ' +
			'<input type="button" class="annuleren" value="Annuleren" /> ' +
			'</form>';
		bewerkContainer.html(bewerkForm);
		bewerkContainer.find('form').on('submit', submitPost);
		bewerkContainer.find('input.voorbeeld').on('click', CsrBBPreview.bind(null, 'forumBewerkBericht', 'bewerkPreview'));
		bewerkContainer.find('input.annuleren').on('click', restorePost);

		let $forumBewerkBericht = $('#forumBewerkBericht');
		$forumBewerkBericht.val(data);
		$forumBewerkBericht.autosize();
		$forumBewerkBericht.markItUp(bbCodeSet);
		$(bewerkContainer).parent().children('td.auteur:first').append('<div id="bewerk-melding">Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]</div>');
		$('#bewerk-melding').slideDown(200);
		$('#forumPosten').css('visibility', 'hidden');
	});
	return false;
}

function forumCiteren(postId) {
	$.ajax({
		url: '/forum/citeren/' + postId,
		method: 'POST',
	}).done((data) => {
		let bericht = $('#forumBericht');
		bericht.val(bericht.val() + data);
		$(window).scrollTo('#reageren');
	});
	// We returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
	// Het werkt dan dus nog wel als javascript uit staat.
	return false;
}

function statsGrafiek() {
	const detailsDiv = $('#stats_grafiek_details'),
		overviewDiv = $('#stats_grafiek_overview');

	if (!detailsDiv.length || !overviewDiv.length) {
		return;
	}

	$.post('/forum/grafiekdata').done(function (data) {

		// helper for returning the weekends in a period

		function weekendAreas(axes) {

			let markings = [];
			let d = new Date(axes.xaxis.min);

			// go to the first Saturday

			d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7));
			d.setUTCSeconds(0);
			d.setUTCMinutes(0);
			d.setUTCHours(0);

			let i = d.getTime();

			// when we don't set yaxis, the rectangle automatically
			// extends to infinity upwards and downwards

			do {
				markings.push({
					xaxis: {
						from: i, to: i + 2 * 24 * 60 * 60 * 1000,
					},
				});
				i += 7 * 24 * 60 * 60 * 1000;
			} while (i < axes.xaxis.max);

			return markings;
		}


		let options = {
			grid: {
				markings: weekendAreas,
				backgroundColor: '#FFFFFF',
			},
			selection: {
				mode: 'x',
			},
			xaxis: {
				mode: 'time',
				timeformat: '%d %b', // 20%y
				monthNames: ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
				tickLength: 5,
			},
			series: {
				lines: {
					show: true,
					lineWidth: 1,
				},
				shadowSize: 0,
			},
		};

		// toon totaal alleen in overview
		let totaal = [data[0]];
		data.splice(0, 1);

		options['legend'] = {
			show: false,
		};
		let overview = $.plot(overviewDiv, totaal, options);

		options['legend'] = {
			sorted(a, b) {
				// sort alphabetically in ascending order
				return a.label === b.label ? 0 : (a.label > b.label ? 1 : -1);
			},
		};
		let plot = $.plot(detailsDiv, data, options);

		const getMaxY = function (rangeFrom, rangeTo) {
			let maxy = 0;
			$.each(data, function (key, val) {
				$.each(val['data'], function () {
					if (this[0] > rangeFrom && this[0] < rangeTo) {
						maxy = this[1] > maxy ? this[1] : maxy;
					}
				});
			});
			return maxy;
		};

		// now connect the two

		detailsDiv.bind('plotselected', (event, ranges) => {

			// do the zooming
			$.each(plot.getXAxes(), function (_, axis) {
				axis.options.min = ranges.xaxis.from;
				axis.options.max = ranges.xaxis.to;
			});

			// update scale
			let maxy = 1.05 * getMaxY(ranges.xaxis.from, ranges.xaxis.to);

			$.each(plot.getYAxes(), (_, axis) => {
				axis.options.min = 0;
				axis.options.max = maxy;
			});

			plot.setupGrid();
			plot.draw();
			plot.clearSelection();

			// don't fire event on the overview to prevent eternal loop
			overview.setSelection(ranges, true);
		});

		overviewDiv.bind('plotselected', function (event, ranges) {
			plot.setSelection(ranges);
		});

	}).fail(alert);

}

$(function () {

	let $textarea = $('#forumBericht');
	let $concept = $('#forumConcept');

	// The last value that we pinged
	let lastPing = null;
	if ($concept.length === 1) {

		/*var ping = */
		setInterval(() => {
			let pingValue = $textarea.val() !== $textarea.attr('origvalue');
			if (pingValue !== false || lastPing !== false) {
				$.post($concept.attr('data-url'), {
					ping: pingValue,
				}).done(domUpdate);
				lastPing = pingValue;
			}
		}, 60000);
		/*var autosave;
     $textarea.focusin(function () {
     autosave = setInterval(saveConceptForumBericht, 3000);
     });
     $textarea.focusout(function () {
     clearInterval(autosave);
     });*/
	}

	// naar juiste forumreactie scrollen door hash toe te voegen
	if (!window.location.hash && window.location.pathname.substr(0, 15) === '/forum/reactie/') {
		let reactieid = parseInt(window.location.pathname.substr(15), 10);
		window.location.hash = '#' + reactieid;
	}

	$textarea.on('keyup', (event) => {
		if (event.keyCode === 13) { // enter
			CsrBBPreview('forumBericht', 'berichtPreview');
		}
	});

	let $nieuweTitel = $('#nieuweTitel');

	if ($nieuweTitel.length !== 0) {
		let $draadMelding = $('#draad-melding');
		$nieuweTitel.on('focusin', () => $draadMelding.slideDown(200));
		$nieuweTitel.on('focusout', () => $draadMelding.slideUp(200));
	}

	$('.togglePasfoto').on('click', function () {
		$(this).parent().find('.forumpasfoto').toggleClass('verborgen');
	});

	$('.auteur').hoverIntent(function () {$(this).find('a.forummodknop').css('opacity', '1');}, function () {$(this).find('a.forummodknop').css('opacity', '0');});

	$('a.citeren').on('click', function () {
		let postid = $(this).attr('data-citeren');
		forumCiteren(postid);
	});

	statsGrafiek();
});
