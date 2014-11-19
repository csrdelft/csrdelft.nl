<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/layout/js/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript">{literal}
function makePlot(){

	var timespan=40;
	var options={
		grid: { hoverable: true, clickable: true },
		xaxis: { mode: "time", timeformat: "%d-%m<br />%y"},
		yaxis: { tickFormatter: function(v, axis){ return '€ '+v.toFixed(axis.tickDecimals); }}
	};
	var plot=jQuery.plot('#saldografiek', [], options);

	function updateData(timespan){
		jQuery.ajax({
			url:'/tools/saldodata.php?uid={/literal}{$profiel->getUid()}{literal}&timespan='+timespan,
			dataType: 'json',
			success: function(data){
				plot.setData(data);
				plot.setupGrid();
				plot.draw();
			}
		});
	}

	jQuery('<div class="button" style="cursor: pointer; font-size: 12px; line-height: 12px; position: absolute; padding: 0; left: 10px; bottom: 0;" title="Verder terug in de tijd...">&laquo;</div>').appendTo("#saldografiek").click(function (e) {
		timespan=timespan*2;
		if(timespan>(15*365)){
			return;
		}
		updateData(timespan);
	});
	updateData(timespan);

	var previousPoint = null;
	jQuery("#saldografiek").bind("plothover", function (event, pos, item) {
		if(item){
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;

				jQuery("#tooltip").remove();

				thedate=new Date(item.datapoint[0]);
				var x = thedate.getDate()+'-'+(thedate.getMonth()+1)+'-'+thedate.getFullYear();
				var y = item.datapoint[1].toFixed(2);

				//door de threshold-plugin is er een andere serie gemaakt, we nemen het oude label over.
				if(item.series.label==null){
					item.series.label=item.series.originSeries.label+': ROOD!';
				}
				showTooltip(item.pageX, item.pageY, item.series.label + " @ " + x + " = € " + y);
			}
		}else{
			jQuery("#tooltip").remove();
			previousPoint = null;
		}
	});
}

function showTooltip(x, y, contents) {
	jQuery('<div id="tooltip">' + contents + '</div>').css( {
		position: 'absolute',
		display: 'none',
		top: y + 5,
		left: x + 5,
		border: '1px solid #fdd',
		padding: '2px',
		'background-color': '#fee',
		opacity: 0.80
	}).appendTo("body").fadeIn(150);
}
{/literal}
</script>
<div id="saldografiek" class="verborgen" style="width: 670px; height: 220px;"></div>
{if LoginModel::getUid() !== $profiel->getUid()}
	<br /><a class="btn" onclick="jQuery('#saldografiek').show(); makePlot(); jQuery(this).remove()">Toon saldografiek</a>
{else}
	<script>{literal}
		jQuery(document).ready(function(){
			jQuery("#saldografiek").show();
			makePlot();
		});
	{/literal}
	</script>
{/if}