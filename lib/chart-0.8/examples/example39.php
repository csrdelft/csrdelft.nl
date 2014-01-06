<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example39");
$chart->plot($data, false, "red", "gradient", "black", 8|2|4);
$chart->stroke();
?>

