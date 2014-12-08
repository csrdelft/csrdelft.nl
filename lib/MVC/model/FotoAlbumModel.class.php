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
		// verwijder niet bestaande subalbums en fotos uit de database
		$this->cleanup($fotoalbum);
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
Voltooid met {$errors} errors. Dit album bevat {$albums} sub-albums en in totaal {$fotos} foto's.
HTML;
		if (defined('RESIZE_OUTPUT')) {
			echo '<br />' . $msg;
			exit;
		} else {
			setMelding($msg, $errors > 0 ? 2 : 1);
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
		$album->subdir = str_replace($album->dirname, $nieuwenaam, $album->subdir);
		return rename($oldpath, $album->path);
	}

	public function setAlbumCover(FotoAlbum $album, Foto $cover) {
		$success = true;
		$toggle = false;
		// find old cover
		foreach ($album->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				if ($foto->getFullPath() === $cover->getFullPath()) {
					$toggle = true;
				}
				$path = $foto->getThumbPath();
				$success &= rename($path, str_replace('folder', '', $path));
				$path = $foto->getResizedPath();
				$success &= rename($path, str_replace('folder', '', $path));
				$path = $foto->getFullPath();
				$success &= rename($path, str_replace('folder', '', $path));
				if ($toggle) {
					return $success;
				}
			}
		}
		// set new cover
		$path = $cover->getThumbPath();
		$success &= rename($path, substr_replace($path, 'folder', strrpos($path, '.'), 0));
		$path = $cover->getResizedPath();
		$success &= rename($path, substr_replace($path, 'folder', strrpos($path, '.'), 0));
		$path = $cover->getFullPath();
		$success &= rename($path, substr_replace($path, 'folder', strrpos($path, '.'), 0));
		return $success;
	}

	public function cleanup(FotoAlbum $fotoalbum) {
		foreach ($this->find('subdir LIKE ?', array($fotoalbum->subdir . '%')) as $album) {
			if (!$album->exists()) {
				foreach (FotoModel::instance()->find('subdir LIKE ?', array($album->subdir . '%')) as $foto) {
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

}
