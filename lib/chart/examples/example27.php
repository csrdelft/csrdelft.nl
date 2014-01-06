<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example27");
$chart->set_font("/usr/share/texmf/fonts/type1/adobe/utopia/putb8a.pfb",
		 "type1", 16);
$chart->set_title("A Big Utopia");
$chart->plot($data);
$chart->stroke();
?>

