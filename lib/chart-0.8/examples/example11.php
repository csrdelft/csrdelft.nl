<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example11");
$chart->plot($data, false, "black", "cross", false, 4);
$chart->stroke();
?>

