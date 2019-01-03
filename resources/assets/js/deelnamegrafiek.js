import $ from 'jquery';

export function initDeelnamegrafiek(parent) {
	$(parent).find('.ctx-deelnamegrafiek').each((i, el) => {
		let $el = $(el);
		$.plot($el, [
			{
				data: $el.data('series-1'),
				label: '',
				color: '#FFCBDB'
			}, {
				data: $el.data('series-0'),
				label: '',
				color: '#AFD8F8'
			}
		], {
			series: {
				bars: {
					show: true,
					lineWidth: 20
				},
				stack: true
			}, yaxis: {
				tickDecimals: 0
			},
			xaxis: {
				autoscaleMargin: .01
			},
			xaxes: [{
				mode: 'time',
				minTickSize: $el.data('step'),
			}]
		});
	});
}
