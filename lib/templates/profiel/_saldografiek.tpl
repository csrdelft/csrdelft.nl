<!--[if IE]><script language="javascript" type="text/javascript" src="/layout/js/flot/excanvas.js"></script><![endif]-->
<script type="text/javascript">{literal}
function makePlot(){
	jQuery.plot(
		jQuery("#saldografiek"), 
		{/literal}{$saldografiek}{literal},
			{
				grid: { hoverable: true, clickable: true },
				xaxis: { mode: "time", timeformat: "%y/%m/%d"},
				yaxis: { tickFormatter: function(v, axis){ return '€ '+v.toFixed(axis.tickDecimals); }}
			}
	);
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
<div id="saldografiek" style="display: none; width: 600px; height: 220px;"></div>

{if !$loginlid->isSelf($lid->getUid())}
	<br /><a class="knop" onclick="jQuery('#saldografiek').show(); makePlot(); jQuery(this).remove()">Toon saldografiek</a>
{else}
	<script>{literal}
		jQuery(document).ready(function(){
			jQuery("#saldografiek").show();
			makePlot();
		});
	{/literal}
	</script>
{/if}



