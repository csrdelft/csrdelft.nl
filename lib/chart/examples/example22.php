<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example22");
$chart->plot($data4, false, "blue");
$chart->add_legend("Gross", "blue");
$chart->plot($data);
$chart->add_legend("Net");
$chart->stroke();
?>

