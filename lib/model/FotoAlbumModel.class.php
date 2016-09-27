<?php

/**
 * FotoAlbumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoAlbumModel extends PersistenceModel {

	const ORM = 'FotoAlbum';
	const DIR = 'fotoalbum/';

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
		if (AccountModel::isValidUid($path) AND ProfielModel::existsUid($path)) {
			require_once 'model/entity/fotoalbum/FotoTagAlbum.class.php';
			$album = new FotoTagAlbum($path);
		} else {
			$album = new FotoAlbum($path);
		}
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
			$oldUUID = $foto->getUUID();
			// updaten gaat niet vanwege primary key
			FotoModel::instance()->delete($foto);
			$foto->subdir = str_replace($oldDir, $newDir, $foto->subdir);
			FotoModel::instance()->create($foto);
			foreach (FotoTagsModel::instance()->find('refuuid = ?', array($oldUUID)) as $tag) {
				// updaten gaat niet vanwege primary key
				FotoTagsModel::instance()->delete($tag);
				$tag->refuuid = $foto->getUUID();
				FotoTagsModel::instance()->create($tag);
			}
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
					FotoTagsModel::instance()->verwijderFotoTags($foto);
				}
				$this->delete($album);
			}
		}
	}

}

class FotoModel extends PersistenceModel {

	const ORM = 'Foto';
	const DIR = 'fotoalbum/';

	protected static $instance;

	/**
	 * @override parent::retrieveByUUID($UUID)
	 */
	public function retrieveByUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$path = explode('/', $parts[0]);
		$filename = array_pop($path);
		$subdir = implode('/', $path) . '/';
		return $this->retrieveByPrimaryKey(array($subdir, $filename));
	}

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
			FotoTagsModel::instance()->verwijderFotoTags($foto);
		}
		return $ret;
	}

}

class FotoTagsModel extends PersistenceModel {

	const ORM = 'FotoTag';
	const DIR = 'fotoalbum/';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'wanneer DESC';

	public function getTags(Foto $foto) {
		return $this->find('refuuid = ?', array($foto->getUUID()));
	}

	public function addTag(Foto $foto, $uid, $x, $y, $size) {
		if (!ProfielModel::existsUid($uid)) {
			throw new Exception('Profiel bestaat niet');
		}
		$tag = new FotoTag();
		$tag->refuuid = $foto->getUUID();
		$tag->keyword = $uid;
		$tag->door = LoginModel::getUid();
		$tag->wanneer = getDateTime();
		$tag->x = (int) $x;
		$tag->y = (int) $y;
		$tag->size = (int) $size;
		if ($this->exists($tag)) {
			return $this->retrieve($tag);
		} else {
			parent::create($tag);
			return $tag;
		}
	}

	public function removeTag($refuuid, $keyword) {
		return $this->deleteByPrimaryKey(array($refuuid, $keyword));
	}

	public function verwijderFotoTags(Foto $foto) {
		foreach ($this->getTags($foto) as $tag) {
			$this->delete($tag);
		}
	}

}
