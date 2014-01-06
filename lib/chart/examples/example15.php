<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example15");
$chart->plot($data, false, "red", "gradient", "black", 4);
$chart->stroke();
?>

