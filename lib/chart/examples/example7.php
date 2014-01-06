<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example7");
$chart->plot($data, false, "black", "square");
$chart->stroke();
?>

