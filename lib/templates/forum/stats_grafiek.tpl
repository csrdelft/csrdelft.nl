<script type="text/javascript">

	$.post('/forum/grafiekdata').done(function (data, textStatus, jqXHR) {

		var d = data;

		// first correct the timestamps - they are recorded as the daily
		// midnights in UTC+0100, but Flot always displays dates in UTC
		// so we have to add one hour to hit the midnights in the plot

		for (var i = 0; i < d.length; ++i) {
			d[i][0] += 60 * 60 * 1000;
		}

		// helper for returning the weekends in a period

		function weekendAreas(axes) {

			var markings = [];
			var d = new Date(axes.xaxis.min);

			// go to the first Saturday

			d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7));
			d.setUTCSeconds(0);
			d.setUTCMinutes(0);
			d.setUTCHours(0);

			var i = d.getTime();

			// when we don't set yaxis, the rectangle automatically
			// extends to infinity upwards and downwards

			do {
				markings.push({
					xaxis: {
						from: i, to: i + 2 * 24 * 60 * 60 * 1000
					}
				});
				i += 7 * 24 * 60 * 60 * 1000;
			} while (i < axes.xaxis.max);

			return markings;
		}

		var options = {
			xaxis: {
				mode: "time",
				tickLength: 5
			},
			selection: {
				mode: "x"
			},
			grid: {
				markings: weekendAreas
			}
		};

		var plot = $.plot("#placeholder", [d], options);

		var overview = $.plot("#overview", [d], {
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				shadowSize: 0
			},
			xaxis: {
				ticks: [],
				mode: "time"
			},
			yaxis: {
				ticks: [],
				min: 0,
				autoscaleMargin: 0.1
			},
			selection: {
				mode: "x"
			}
		});

		// now connect the two

		$("#placeholder").bind("plotselected", function (event, ranges) {

			// do the zooming
			$.each(plot.getXAxes(), function (_, axis) {
				var opts = axis.options;
				opts.min = ranges.xaxis.from;
				opts.max = ranges.xaxis.to;
			});
			plot.setupGrid();
			plot.draw();
			plot.clearSelection();

			// don't fire event on the overview to prevent eternal loop

			overview.setSelection(ranges, true);
		});

		$("#overview").bind("plotselected", function (event, ranges) {
			plot.setSelection(ranges);
		});

		// Add the Flot version string to the footer

		$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");

	}).fail(alert);

</script>

<style>
	.demo-container {
		box-sizing: border-box;
		width: 750px;
		padding: 20px 15px 15px 15px;
		margin: 15px auto 30px auto;
		border: 1px solid #ddd;
		background: #fff;
		background: linear-gradient(#f6f6f6 0, #fff 50px);
		background: -o-linear-gradient(#f6f6f6 0, #fff 50px);
		background: -ms-linear-gradient(#f6f6f6 0, #fff 50px);
		background: -moz-linear-gradient(#f6f6f6 0, #fff 50px);
		background: -webkit-linear-gradient(#f6f6f6 0, #fff 50px);
		box-shadow: 0 3px 10px rgba(0,0,0,0.15);
		-o-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
		-ms-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
		-moz-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
		-webkit-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
	}

	.demo-placeholder {
		width: 100%;
		height: 100%;
		font-size: 14px;
		line-height: 1.2em;
	}
</style>

<div class="demo-container" style="height: 450px;">
	<div id="placeholder" class="demo-placeholder"></div>
</div>

<div class="demo-container" style="height: 150px;">
	<div id="overview" class="demo-placeholder"></div>
</div>
