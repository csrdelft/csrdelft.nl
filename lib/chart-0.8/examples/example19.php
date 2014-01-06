<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example19");
$chart->plot($data, false, "blue", "gradient", "black", 7);
$chart->plot($data, false, "red", "gradient", "black", 0);
$chart->stroke();
?>

