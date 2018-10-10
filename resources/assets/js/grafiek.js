/**
 * Laad alle flot in voor grafiekjes.
 */

import 'flot';
import 'flot/jquery.flot.pie';
import 'flot/jquery.flot.stack';
import 'flot/jquery.flot.threshold';
import 'flot/jquery.flot.time';
import 'flot/jquery.flot.selection';
import 'jquery.flot.tooltip';

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
						radius: 2/3,
						formatter: function(label, series) {
							return `<div class="pie-chart-label">${label}<br/>${Math.round(series.percent)}%</div>`;
						},
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
		piechart: function(label, series) {
			return `<div class="pie-chart-label">${label}<br/>${Math.round(series.percent)}%</div>`;
		}
	}
};
