<?php

/**
 * DragObject.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Stores the screen coordinates of a dragable object in the session variable.
 * @see /htdocs/tools/dragobject.php
 */
class DragObjectModel {

	public static function getCoords($id, &$top, &$left) {

		if (array_key_exists('dragobject', $_SESSION) && array_key_exists($id, $_SESSION['dragobject'])) {

			$top = (int) $_SESSION['dragobject'][$id]['top'];
			$left = (int) $_SESSION['dragobject'][$id]['left'];
		}
	}
}
