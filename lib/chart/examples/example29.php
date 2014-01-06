<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example29");
$chart->plot($data, $data4, "blue", "fill");
$chart->stroke();
?>

