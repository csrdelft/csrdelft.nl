<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/layout/js/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
	function makePlot() {

		var timespan = 11;
		var options = {
			grid: {
				hoverable: true,
				clickable: true
			},
			xaxis: {
				mode: "time",
				timeformat: "%d %b 20%y",
				monthNames: ["jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec"]
			},
			yaxis: {
				tickDecimals: 2,
				tickFormatter: function (v, axis) {
					return '€ ' + v.toFixed(axis.tickDecimals);
				}
			},
			tooltip: true,
			tooltipOpts: {
				content: "%s: %y<br/>%x",
				lines: {
					track: true
				}
			}
		};
		var plot = jQuery.plot('#saldografiek', [], options);

		function updateData(timespan) {
			jQuery.ajax({
				url: '/leden/saldo/{$profiel->uid}/' + timespan,
				dataType: 'json',
				success: function (data) {
					plot.setData(data);
					plot.setupGrid();
					plot.draw();
				}
			});
		}

		jQuery('<div style="cursor: pointer; font-size: 12px; line-height: 12px; position: absolute; padding: 0; left: 10px; bottom: 0;" title="Verder terug in de tijd...">&laquo;</div>').appendTo("#saldografiek").click(function (event) {
			timespan = timespan * 2;
			if (timespan > (15 * 365)) {
				return;
			}
			updateData(timespan);
		});
		updateData(timespan);
	}
</script>
<div id="saldografiek" class="verborgen" style="width: 670px; height: 220px;"></div>
{if CsrDelft\model\security\LoginModel::getUid() !== $profiel->uid}
	<br /><a class="btn" onclick="jQuery('#saldografiek').show();
			makePlot();
			jQuery(this).remove()">Toon saldografiek</a>
{else}
	<script>
		jQuery(document).ready(function () {
			jQuery("#saldografiek").show();
			makePlot();
		});
	</script>
{/if}
