<?php

require_once 'MVC/model/entity/Foto.class.php';

/**
 * FotoAlbumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoAlbumModel {

	public static function getFotoAlbum($path) {
		if (!endsWith($path, '/')) {
			$path .= '/';
		}
		if (!FotoAlbumController::magBekijken($path)) {
			return false;
		}
		$album = new FotoAlbum($path);
		if (!$album->exists()) {
			return null;
		}
		return $album;
	}

	public static function verwerkFotos(FotoAlbum $album) {
		$path = $album->path . '_thumbs';
		if (!file_exists($path)) {
			mkdir($path);
			chmod($path, 0755);
		}
		$path = $album->path . '_resized';
		if (!file_exists($path)) {
			mkdir($path);
			chmod($path, 0755);
		}
		foreach ($album->getFotos(true) as $foto) {
			if (!$foto->hasThumb()) {
				$foto->maakThumb();
			}
			if (!$foto->hasResized()) {
				$foto->maakResized();
			}
		}
	}

	public static function getMostRecentFotoAlbum() {
		$album = FotoAlbumModel::getFotoAlbum(PICS_PATH . 'fotoalbum/');
		if (!$album) {
			return null;
		}
		return $album->getMostRecentSubAlbum();
	}

	public static function verwijderFoto(Foto $foto) {
		$ret = true;
		$ret &= unlink($foto->directory->path . $foto->filename);
		if ($foto->hasResized()) {
			$ret &= unlink($foto->getResizedPad());
		}
		if ($foto->hasThumb()) {
			$ret &= unlink($foto->getThumbPad());
		}
		return $ret;
	}

	public static function hernoemAlbum(FotoAlbum $album, $nieuwenaam) {
		if (!valid_filename($nieuwenaam)) {
			throw new Exception('Ongeldige naam');
		}
		return rename($album->path, str_replace($album->dirname, $nieuwenaam, $album->path));
	}

	public static function setAlbumCover(FotoAlbum $album, Foto $cover) {
		$ret = true;
		// find old cover
		foreach ($album->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				if ($foto->getPad() === $cover->getPad()) {
					return $ret;
				}
				$old = $foto->getPad();
				$ret &= rename($old, str_replace('folder', '', $old));
				$old = $foto->getResizedPad();
				$ret &= rename($old, str_replace('folder', '', $old));
				$old = $foto->getThumbPad();
				$ret &= rename($old, str_replace('folder', '', $old));
			}
		}
		// set new cover
		$old = $cover->getPad();
		$ret &= rename($old, substr_replace($old, 'folder', strrpos($old, '.'), 0));
		$old = $cover->getResizedPad();
		$ret &= rename($old, substr_replace($old, 'folder', strrpos($old, '.'), 0));
		$old = $cover->getThumbPad();
		$ret &= rename($old, substr_replace($old, 'folder', strrpos($old, '.'), 0));
		return $ret;
	}

}
