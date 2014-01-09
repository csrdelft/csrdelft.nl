<?php

function getDragObjectCoords($id, &$top, &$left) {
	
	if (array_key_exists('dragobject', $_SESSION) && array_key_exists($id, $_SESSION['dragobject'])) {
		
		$top = (int) $_SESSION['dragobject'][$id]['top'];
		$left = (int) $_SESSION['dragobject'][$id]['left'];
	}
	
	echo print_r($_SESSION['dragobject']);
}
