<?php

/**
 * FotoAlbumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoAlbumModel extends PersistenceModel {

	const orm = 'FotoAlbum';

	protected static $instance;

	public function create(PersistentEntity $album) {
		if (!file_exists($album->path)) {
			mkdir($album->path);
			chmod($album->path, 0755);
		}
		$album->owner = LoginModel::getUid();
		return parent::create($album);
	}

	public function delete(PersistentEntity $album) {
		$path = $album->path . '_resized';
		if (file_exists($path)) {
			rmdir($path);
		}
		$path = $album->path . '_thumbs';
		if (file_exists($path)) {
			rmdir($path);
		}
		if (file_exists($album->path)) {
			rmdir($album->path);
		}
		return parent::delete($album);
	}

	public function getFotoAlbum($path) {
		if (strpos($path, '/_') !== false) {
			return null;
		}
		$album = new FotoAlbum($path);
		if (!$album->exists()) {
			return null;
		}
		if (!$album->magBekijken()) {
			return false;
		}
		return $album;
	}

	public function verwerkFotos(FotoAlbum $fotoalbum) {
		//define('RESIZE_OUTPUT', null);
		//echo '<h1>Fotoalbum verwerken: ' . $album->dirname . '</h1>Dit kan even duren...<br />';
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fotoalbum->path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
		$albums = 0;
		$fotos = 0;
		$errors = 0;
		foreach ($iterator as $path => $object) {
			// skip _thumbs & _resized
			if (strpos($path, '/_') !== false) {
				continue;
			}
			try {
				// FotoAlbum
				if ($object->isDir()) {
					$albums++;
					$album = new FotoAlbum($path);
					if (!$this->exists($album)) {
						$this->create($album);
					}
					if (false === @chmod($path, 0755)) {
						throw new Exception('Geen eigenaar van: ' . $path);
					}
				}
				// Foto
				else {
					$filename = basename($path);
					if ($filename === 'Thumbs.db') {
						unlink($path);
						continue;
					}
					$fotos++;
					$album = new FotoAlbum(dirname($path));
					$foto = new Foto($filename, $album, true);
					if (!$foto->exists()) {
						throw new Exception('Foto bestaat niet: ' . $foto->directory . $foto->filename);
					}
					FotoModel::instance()->verwerkFoto($foto);
					if (false === @chmod($path, 0644)) {
						throw new Exception('Geen eigenaar van: ' . $path);
					}
				}
			} catch (Exception $e) {
				$errors++;
				if (defined('RESIZE_OUTPUT')) {
					debugprint($e->getMessage());
				} else {
					setMelding($e->getMessage(), -1);
				}
			}
		}
		// instellen als laatste
		touch($fotoalbum->path);
		$msg = <<<HTML
<br />Voltooid met:
<br />{$albums} albums
<br />{$fotos} fotos
<br />{$errors} errors
HTML;
		if (defined('RESIZE_OUTPUT')) {
			echo $msg;
			exit;
		} else {
			setMelding($msg, 1);
		}
	}

	public function getMostRecentFotoAlbum() {
		$album = $this->getFotoAlbum(PICS_PATH . 'fotoalbum/');
		if (!$album) {
			return null;
		}
		return $album->getMostRecentSubAlbum();
	}

	public function hernoemAlbum(FotoAlbum $album, $nieuwenaam) {
		if (!valid_filename($nieuwenaam)) {
			throw new Exception('Ongeldige naam');
		}
		$oldpath = $album->path;
		$album->path = str_replace($album->dirname, $nieuwenaam, $album->path);
		return rename($oldpath, $album->path);
	}

	public function setAlbumCover(FotoAlbum $album, Foto $cover) {
		$ret = true;
		// find old cover
		foreach ($album->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				if ($foto->getFullPath() === $cover->getFullPath()) {
					return $ret;
				}
				$old = $foto->getFullPath();
				$ret &= rename($old, str_replace('folder', '', $old));
				$old = $foto->getResizedPath();
				$ret &= rename($old, str_replace('folder', '', $old));
				$old = $foto->getThumbPath();
				$ret &= rename($old, str_replace('folder', '', $old));
			}
		}
		// set new cover
		$old = $cover->getFullPath();
		$ret &= rename($old, substr_replace($old, 'folder', strrpos($old, '.'), 0));
		$old = $cover->getResizedPath();
		$ret &= rename($old, substr_replace($old, 'folder', strrpos($old, '.'), 0));
		$old = $cover->getThumbPath();
		$ret &= rename($old, substr_replace($old, 'folder', strrpos($old, '.'), 0));
		return $ret;
	}

	public function cleanup() {
		foreach ($this->find() as $album) {
			if (!$album->exists()) {
				foreach (FotoModel::instance()->find('subdir = ?', array($album->subdir)) as $foto) {
					FotoModel::instance()->delete($foto);
				}
				$this->delete($album);
			}
		}
	}

}

class FotoModel extends PersistenceModel {

	const orm = 'Foto';

	protected static $instance;

	/**
	 * Create database entry if foto does not exist.
	 * 
	 * @param PersistentEntity $foto
	 * @param array $attributes
	 * @return mixed false on failure
	 */
	public function retrieveAttributes(PersistentEntity $foto, array $attributes) {
		$this->verwerkFoto($foto);
		return parent::retrieveAttributes($foto, $attributes);
	}

	public function create(PersistentEntity $foto) {
		$foto->owner = LoginModel::getUid();
		$foto->rotation = 0;
		parent::create($foto);
	}

	public function verwerkFoto(Foto $foto) {
		if (!$this->exists($foto)) {
			$this->create($foto);
			chmod($foto->getFullPath(), 0644);
		}
		if (!$foto->hasThumb()) {
			$foto->createThumb();
		}
		if (!$foto->hasResized()) {
			$foto->createResized();
		}
	}

	public function verwijderFoto(Foto $foto) {
		$ret = true;
		$ret &= unlink($foto->directory . $foto->filename);
		if ($foto->hasResized()) {
			$ret &= unlink($foto->getResizedPath());
		}
		if ($foto->hasThumb()) {
			$ret &= unlink($foto->getThumbPath());
		}
		if ($ret) {
			$this->delete($foto);
		}
		return $ret;
	}

	public function cleanup() {
		foreach ($this->find() as $foto) {
			if (!$foto->exists()) {
				$this->delete($foto);
			}
		}
	}

}
