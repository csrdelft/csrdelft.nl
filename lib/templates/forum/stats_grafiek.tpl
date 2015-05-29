<script type="text/javascript">

	$.post('/forum/grafiekdata').done(function (data, textStatus, jqXHR) {

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

		var plot = $.plot("#placeholder", data, {
			grid: {
				markings: weekendAreas
			},
			selection: {
				mode: "x"
			},
			xaxis: {
				mode: "time",
				timeformat: "%d %b 20%y",
				monthNames: ["jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec"],
				tickLength: 5
			},
			yaxis: {
				// log scale
				transform: function (v) {
					return Math.log(v);
				},
				inverseTransform: function (v) {
					return Math.exp(v);
				}
			}
		});

		var overview = $.plot("#overview", data, {
			legend: {
				show: false
			},
			selection: {
				mode: "x"
			},
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
				// log scale
				transform: function (v) {
					return Math.log(v);
				},
				inverseTransform: function (v) {
					return Math.exp(v);
				}
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

<div class="grafiek-container" style="height: 450px;">
	<div id="placeholder" class="grafiek-placeholder"></div>
</div>

<div class="grafiek-container" style="height: 150px;">
	<div id="overview" class="grafiek-placeholder"></div>
</div>
