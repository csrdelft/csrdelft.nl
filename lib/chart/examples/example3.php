<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example3-1");
$chart->plot($data, false, "white");
$chart->set_grid_color("black");
$chart->set_background_color("blue", "ForestGreen");
$chart->set_title("Title, background and border");
$chart->stroke();
?>

