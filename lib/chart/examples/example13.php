<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example13");
$chart->plot($data, false, "red", "gradient", "black", 1);
$chart->stroke();
?>

