<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example31");
$chart->set_font("/usr/share/texmf/fonts/type1/adobe/utopia/putb8a.pfb",
		 "type1", 11);
$chart->set_title("A Package For Generating Charts");
$chart->plot($data4, false, "red", "gradient", "white", 0);
$chart->plot($data, false, "blue", "gradient", "white", 4);
$chart->plot($data4, false, "gray");
$chart->plot($data4, false, "gray", "box", "black");
$chart->add_legend("Other Things", "red");
$chart->add_legend("More Stuff", "blue");
$chart->stroke();
?>

