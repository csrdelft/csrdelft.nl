<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/layout/js/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">
	function makePlot() {

		var timespan = 40;
		var options = {
			grid: {
				hoverable: true,
				clickable: true
			},
			xaxis: {
				mode: "time",
				timeformat: "%d %b %y",
				monthNames: ["jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec"],
			},
			yaxis: {
				tickFormatter: function (v, axis) {
					return '€ ' + v.toFixed(axis.tickDecimals);
				}
			},
			tooltip: true,
			tooltipOpts: {
				content: "%s: %y.2<br/>%x",
				lines: {
					track: true,
					threshold: 0.5
				}
			}
		};
		var plot = jQuery.plot('#saldografiek', [], options);

		function updateData(timespan) {
			jQuery.ajax({
				url: '/tools/saldodata.php?uid={$profiel->getUid()}&timespan=' + timespan,
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
{if LoginModel::getUid() !== $profiel->getUid()}
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