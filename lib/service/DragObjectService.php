<?php

namespace CsrDelft\service;

/**
 * DragObjectModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Stores the screen coordinates of a dragable object in the session variable.
 * @see ToolsController
 */
class DragObjectService {

	public static function getCoords($id, $top, $left) {
		if (isset($_SESSION['dragobject'][$id])) {
			$top = (int)$_SESSION['dragobject'][$id]['top'];
			$left = (int)$_SESSION['dragobject'][$id]['left'];
		}

		$top = max($top, 0);
		$left = max($left, 0);
		return array('top' => $top, 'left' => $left);
	}

}
