<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example12");
$chart->plot($data, false, "red", "gradient", "black", 0);
$chart->stroke();
?>

