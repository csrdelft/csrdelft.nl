<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example28");
$chart->plot($data, $data4, "blue", "fillgradient", "black", 0);
$chart->stroke();
?>

