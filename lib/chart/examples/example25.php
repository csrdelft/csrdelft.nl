<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example25");
$chart->plot($data, false, "gray", "box", "black");
$chart->stroke();
?>

