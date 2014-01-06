<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example37");
$chart->plot($data, false, "red", "gradient", "black", 8|2);
$chart->stroke();
?>

