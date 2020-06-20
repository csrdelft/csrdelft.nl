/**
 * csrdelft.nl javascript libje...
 */

import $ from 'jquery';

export function initSluitMeldingen() {
	$('#melding').on('click', '.alert', function () {
		$(this).slideUp(400, function () {
			$(this).remove();
		});
	});
}

