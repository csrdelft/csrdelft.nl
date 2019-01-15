import $ from 'jquery';

// Definieer verschillende configuraties voor grafieken.
// Wordt gebruikt in GroepStatistiekView.
window.flot = {
	preset: {
		geslacht: {
			series: {
				pie: {
					show: true,
					radius: 1,
					innerRadius: .5,
					label: {
						show: false
					}
				}
			},
			legend: {
				show: false
			}
		},
		verticale: {
			series: {
				pie: {
					show: true,
					radius: 1,
					label: {
						show: true,
						radius: 2 / 3,
						formatter: (label, series) => `<div class="pie-chart-label">${label}<br/>${Math.round(series.percent)}%</div>`,
						threshold: 0.1
					}
				}
			},
			legend: {
				show: false
			}
		},
		lichting: {
			series: {
				bars: {
					show: true,
					barWidth: 0.5,
					align: 'center',
					lineWidth: 0,
					fill: 1
				}
			},
			xaxis: {
				tickDecimals: 0
			},
			yaxis: {
				tickDecimals: 0
			}
		},
		tijd: {
			xaxes: [{
				mode: 'time'
			}],
			yaxis: {
				tickDecimals: 0
			}
		}
	},
	formatter: {
		piechart: (label, series) => `<div class="pie-chart-label">${label}<br/>${Math.round(series.percent)}%</div>`
	}
};

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

export function initSaldoGrafiek(parent) {
	$(parent).find('.ctx-saldografiek').each((i, el) => {
		let $el = $(el);
		if ($el.data('closed')) {
			let button = $('<a href="#" class="btn btn-primary">Toon saldografiek</a>');
			button.on('click', () => {
				$el.show();
				button.hide();
			});
			button.insertAfter($el);
		} else {
			$el.show();
		}

		let timespan = 11;
		const options = {
			grid: {
				hoverable: true,
				clickable: true
			},
			xaxis: {
				mode: 'time',
				timeformat: '%d %b 20%y',
				monthNames: ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec']
			},
			yaxis: {
				tickDecimals: 2,
				tickFormatter: (v, axis) => '€ ' + v.toFixed(axis.tickDecimals)
			},
			tooltip: true,
			tooltipOpts: {
				content: '%s: %y<br/>%x',
				lines: {
					track: true
				}
			}
		};
		const plot = $.plot($el, [], options);

		function updateData(timespan) {
			$.ajax({
				url: `/leden/saldo/${$el.data('uid')}/${timespan}`,
				dataType: 'json',
				success: (data) => {
					plot.setData(data);
					plot.setupGrid();
					plot.draw();
				}
			});
		}

		$('<div style="cursor: pointer; font-size: 12px; line-height: 12px; position: absolute; padding: 0; left: 10px; bottom: 0;" title="Verder terug in de tijd...">&laquo;</div>')
			.appendTo($el)
			.on('click', () => {
					timespan = timespan * 2;
					if (timespan > (15 * 365)) {
						return;
					}
					updateData(timespan);
				}
			);
		updateData(timespan);
	});
}
