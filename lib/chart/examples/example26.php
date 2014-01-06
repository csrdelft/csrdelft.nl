<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example26");
$chart->set_font("/usr/share/texmf/fonts/type1/adobe/utopia/putb8a.pfb",
		 "type1");
$chart->set_title("Using The Adobe Utopia Font");
$chart->plot($data);
$chart->stroke();
?>

