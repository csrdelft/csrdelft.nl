<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example24");
$chart->plot($data, false, "gray", "triangle", "black");
$chart->stroke();
?>

