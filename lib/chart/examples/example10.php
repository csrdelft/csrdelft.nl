<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example10");
$chart->plot($data, false, "black", "circle", false, 8);
$chart->stroke();
?>

