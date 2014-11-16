<?php

echo '</main>';

$wiki->getBody()->view();

$coords = DragObjectModel::getCoords('modal', 175, 200);
echo '<div id="modal-background"></div><div id="modal" class="outer-shadow dragobject savepos" style="top: ' . $coords['top'] . 'px; left: ' . $coords['left'] . 'px;"></div>';
