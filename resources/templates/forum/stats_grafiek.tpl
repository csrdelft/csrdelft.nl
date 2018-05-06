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


		var options = {
			grid: {
				markings: weekendAreas,
				backgroundColor: "#FFFFFF"
			},
			selection: {
				mode: "x"
			},
			xaxis: {
				mode: "time",
				timeformat: "%d %b", // 20%y
				monthNames: ["jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec"],
				tickLength: 5
			},
			series: {
				lines: {
					show: true,
					lineWidth: 1
				},
				shadowSize: 0
			}
		};

		// toon totaal alleen in overview
		var totaal = [data[0]];
		data.splice(0, 1);

		options["legend"] = {
			show: false
		};
		var overview = $.plot("#overview", totaal, options);

		options["legend"] = {
			sorted: function (a, b) {
				// sort alphabetically in ascending order
				return a.label === b.label ? 0 : (a.label > b.label ? 1 : -1);
			}
		};
		var plot = $.plot("#details", data, options);

		var getMaxY = function (rangeFrom, rangeTo) {
			var maxy = 0;
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

		$("#details").bind("plotselected", function (event, ranges) {

			// do the zooming
			$.each(plot.getXAxes(), function (_, axis) {
				axis.options.min = ranges.xaxis.from;
				axis.options.max = ranges.xaxis.to;
			});

			// update scale
			var maxy = 1.05 * getMaxY(ranges.xaxis.from, ranges.xaxis.to);

			$.each(plot.getYAxes(), function (_, axis) {
				axis.options.min = 0;
				axis.options.max = maxy;
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

	}).fail(alert);

</script>

<br>

<div id="overview" style="height: 200px;"></div>

<br>

<div id="details" style="height: 500px;"></div>

<br>