<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example4");
$chart->plot($data);
$chart->set_frame();
$chart->stroke();
?>

