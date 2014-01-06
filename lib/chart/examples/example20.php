<?php
require('chart.php');
require('data.php');

$chart = new chart(300, 200, "example20");
$chart->set_grid_color("black", false);
$chart->plot($data, false, "blue", "gradient", "black", 7);
$chart->plot($data, false, "red", "gradient", "black", 0);
$chart->stroke();
?>

