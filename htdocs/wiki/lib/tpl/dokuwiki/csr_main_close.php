<?php

echo '</main>';

$wiki->getBody()->view();

$top = 175;
$left = 200;
DragObjectModel::getCoords('modal', $top, $left);
echo '<div id="modal-background"></div><div id="modal" class="outer-shadow dragobject savepos" style="top: ' . $top . 'px; left: ' . $left . 'px;"></div>';
