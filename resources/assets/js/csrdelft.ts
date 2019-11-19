/**
 * csrdelft.nl javascript libje...
 */

import $ from 'jquery';

import {init} from './ctx';

function initSluitMeldingen() {
	$('#melding').on('click', '.alert', function () {
		$(this).slideUp(400, function () {
			$(this).remove();
		});
	});
}

function zijbalkScrollFixed() {
	const elmnt = $('#zijbalk');
	if (!elmnt.length || !elmnt.hasClass('scroll-fixed')) {
		return;
	}

	if (elmnt.hasClass('desktop-only')
		&& /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		elmnt.removeClass('desktop-only scroll-fixed dragobject dragvertical scroll-hover');
		return;
	}

	// adjust to container size
	$(window).on('resize', () => {
		elmnt.css('height', document.documentElement.clientHeight);
	});
	$(window).trigger('resize');

	// fix position on screen
	$(window).on('scroll', () => {
		elmnt.css({
			top: $(window).scrollTop()!,
		});
	});

	// set scroll position
	elmnt.scrollTop(Number(elmnt.attr('data-scrollfix')));

	// remember scroll position
	let trigger = false;

	function saveCoords() {
		$.post('/tools/dragobject', {
			coords: {
				left: elmnt.scrollLeft(),
				top: elmnt.scrollTop(),
			},
			id: 'zijbalk',
		});
		trigger = false;
	}

	elmnt.on('scroll', () => {
		if (!trigger) {
			trigger = true;
			$(window).one('mouseup', saveCoords);
		}
	});

	// show-hide scrollbar
	if (elmnt.hasClass('scroll-hover')) {
		const showscroll = () => {
			if (elmnt.get(0).scrollHeight > elmnt.get(0).clientHeight) {
				elmnt.css({
					'overflow-y': 'scroll',
				});
			}
		};
		const hidescroll = () => {
			elmnt.css({
				'overflow-y': 'hidden',
			});
		};
		elmnt.hover(showscroll, hidescroll);
	}
}

$(() => {
	zijbalkScrollFixed();
	initSluitMeldingen();
	init(document.body);

	const modal = $('#modal');
	if (modal.html() !== '') {
		modal.modal();
	}
});
