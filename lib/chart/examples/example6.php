<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example6");
$chart->plot($data, false, "black", "lines");
$chart->stroke();
?>

