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
			if (false === @chmod($album->path, 0755)) {
				throw new Exception('Geen eigenaar van album: ' . htmlspecialchars($foto->getFullPath()));
			}
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
		$this->opschonen($fotoalbum);
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
						throw new Exception('Geen eigenaar van album: ' . $path);
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
						throw new Exception('Geen eigenaar van foto: ' . $path);
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

	public function hernoemAlbum(FotoAlbum $album, $newName) {
		if (!valid_filename($newName)) {
			throw new Exception('Ongeldige naam');
		}
		// controleer rechten
		$oldDir = $album->subdir;
		if (false === @chmod(PICS_PATH . $oldDir, 0755)) {
			throw new Exception('Geen eigenaar van album: ' . htmlspecialchars(PICS_PATH . $oldDir));
		}

		// nieuwe subdir op basis van path
		$newDir = dirname($oldDir) . '/' . $newName . '/';
		if (false === @rename($album->path, PICS_PATH . $newDir)) {
			$error = error_get_last();
			throw new Exception($error['message']);
		}
		// controleer rechten
		if (false === @chmod(PICS_PATH . $newDir, 0755)) {
			throw new Exception('Geen eigenaar van album: ' . htmlspecialchars(PICS_PATH . $newDir));
		}

		// database in sync houden
		$album->dirname = basename($newDir);
		$album->subdir = $newDir;
		$album->path = PICS_PATH . $newDir;

		foreach ($this->find('subdir LIKE ?', array($oldDir . '%')) as $subdir) {
			// updaten gaat niet vanwege primary key
			$this->delete($subdir);
			$subdir->subdir = str_replace($oldDir, $newDir, $album->subdir);
			$this->create($subdir);
		}
		foreach (FotoModel::instance()->find('subdir LIKE ?', array($oldDir . '%')) as $foto) {
			// updaten gaat niet vanwege primary key
			FotoModel::instance()->delete($foto);
			$foto->subdir = str_replace($oldDir, $newDir, $foto->subdir);
			FotoModel::instance()->create($foto);
		}
		if (false === @rmdir(PICS_PATH . $oldDir)) {
			$error = error_get_last();
			setMelding($error['message'], -1);
		}
		return true;
	}

	public function setAlbumCover(FotoAlbum $album, Foto $cover) {
		$success = true;
		$toggle = false;
		// find old cover
		foreach ($album->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				if ($foto->getFullPath() === $cover->getFullPath()) {
					$foto = $cover;
				}
				$path = $foto->getThumbPath();
				$success &= rename($path, str_replace('folder', '', $path));
				$path = $foto->getResizedPath();
				$success &= rename($path, str_replace('folder', '', $path));
				$path = $foto->getFullPath();
				$success &= rename($path, str_replace('folder', '', $path));
				if ($success) {
					// database in sync houden
					// updaten gaat niet vanwege primary key
					FotoModel::instance()->delete($foto);
					$foto->filename = str_replace('folder', '', $foto->filename);
					FotoModel::instance()->create($foto);
				}
				if ($foto === $cover) {
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
		if ($success) {
			// database in sync houden
			// updaten gaat niet vanwege primary key
			FotoModel::instance()->delete($cover);
			$cover->filename = substr_replace($cover->filename, 'folder', strrpos($cover->filename, '.'), 0);
			FotoModel::instance()->create($cover);
		}
		return $success;
	}

	public function opschonen(FotoAlbum $fotoalbum) {
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
			if (false === @chmod($foto->getFullPath(), 0644)) {
				throw new Exception('Geen eigenaar van foto: ' . htmlspecialchars($foto->getFullPath()));
			}
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
