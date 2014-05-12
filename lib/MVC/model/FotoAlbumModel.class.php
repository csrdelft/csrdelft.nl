<?php

require_once 'MVC/model/entity/Foto.class.php';

/**
 * FotoAlbumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoAlbumModel {

	public static function getMostRecentFotoAlbum() {
		$map = new Map();
		$map->locatie = PICS_PATH . '/';
		$album = new FotoAlbum($map, 'fotoalbum');
		return $album->getMostRecentSubAlbum();
	}

	public static function getJaargangen($actief = null) {
		foreach (glob(PICS_PATH . '/fotoalbum/*', GLOB_ONLYDIR) as $path) {
			$parts = explode('/', $path);
			$name = end($parts);
			if (!startsWith($name, '_')) {
				$dirs[$name] = $name;
			}
		}
		$dirs = array_reverse($dirs);
		$dropdown = '<select onchange="location.href=\'/actueel/fotoalbum/\'+this.value+\'/\';">';
		foreach ($dirs as $value => $description) {
			$dropdown .= '<option value="' . $value . '"';
			if ($value === $actief) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . htmlspecialchars($description) . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

}
