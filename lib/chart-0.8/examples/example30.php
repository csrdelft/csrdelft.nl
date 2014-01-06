<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example30");
$chart->plot($data, $data4, "red", "fillgradient", "white", 0);
$chart->plot($data, false, "black", "square");
$chart->plot($data4, false, "black", "square");
$chart->stroke();
?>

