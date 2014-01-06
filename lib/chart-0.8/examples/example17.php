<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example17");
$chart->plot($data, false, "red", "gradient", "black", 5);
$chart->stroke();
?>

