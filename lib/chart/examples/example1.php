<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example1");
$chart->plot($data);
$chart->stroke();
?>

