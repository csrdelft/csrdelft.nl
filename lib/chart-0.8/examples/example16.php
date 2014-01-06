<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example16");
$chart->plot($data, false, "red", "gradient", "black", 3);
$chart->stroke();
?>

