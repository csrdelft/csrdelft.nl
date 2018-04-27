<?php

namespace CsrDelft\model\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\fotoalbum\FotoTagAlbum;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * FotoAlbumModel.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FotoAlbumModel extends PersistenceModel {

	const ORM = FotoAlbum::class;

	/**
	 * @var FotoModel
	 */
	private $fotoModel;
	/**
	 * @var FotoTagsModel
	 */
	private $fotoTagsModel;

	public function __construct(
		FotoModel $fotoModel,
		FotoTagsModel $fotoTagsModel
	) {
		parent::__construct();

		$this->fotoModel = $fotoModel;
		$this->fotoTagsModel = $fotoTagsModel;
	}

	/**
	 * @param PersistentEntity|FotoAlbum $album
	 * @return string
	 * @throws CsrException
	 */
	public function create(PersistentEntity $album) {
		if (!file_exists($album->path)) {
			mkdir($album->path);
			if (false === @chmod($album->path, 0755)) {
				throw new CsrException('Geen eigenaar van album: ' . htmlspecialchars($album->path));
			}
		}
		$album->owner = LoginModel::getUid();
		return parent::create($album);
	}

	/**
	 * @param PersistentEntity|FotoAlbum $album
	 * @return int
	 */
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
						throw new CsrException('Geen eigenaar van album: ' . $path);
					}
				} // Foto
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
						throw new CsrException('Foto bestaat niet: ' . $foto->directory . $foto->filename);
					}
					$this->fotoModel->verwerkFoto($foto);
					if (false === @chmod($path, 0644)) {
						throw new CsrException('Geen eigenaar van foto: ' . $path);
					}
				}
			} catch (\Exception $e) {
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
		$album = $this->getFotoAlbum(PHOTOS_PATH . 'fotoalbum/');
		if (!$album) {
			return null;
		}
		return $album->getMostRecentSubAlbum();
	}

	public function hernoemAlbum(FotoAlbum $album, $newName) {
		if (!valid_filename($newName)) {
			throw new CsrGebruikerException('Ongeldige naam');
		}
		// controleer rechten
		$oldDir = $album->subdir;
		if (false === @chmod(PHOTOS_PATH . $oldDir, 0755)) {
			throw new CsrException('Geen eigenaar van album: ' . htmlspecialchars(PHOTOS_PATH . $oldDir));
		}

		// nieuwe subdir op basis van path
		$newDir = dirname($oldDir) . '/' . $newName . '/';
		if (false === @rename($album->path, PHOTOS_PATH . $newDir)) {
			$error = error_get_last();
			throw new CsrException($error['message']);
		}
		// controleer rechten
		if (false === @chmod(PHOTOS_PATH . $newDir, 0755)) {
			throw new CsrException('Geen eigenaar van album: ' . htmlspecialchars(PHOTOS_PATH . $newDir));
		}

		// database in sync houden
		$album->dirname = basename($newDir);
		$album->subdir = $newDir;
		$album->path = PHOTOS_PATH . $newDir;

		foreach ($this->find('subdir LIKE ?', array($oldDir . '%')) as $subdir) {
			// updaten gaat niet vanwege primary key
			$this->delete($subdir);
			$subdir->subdir = str_replace($oldDir, $newDir, $album->subdir);
			$this->create($subdir);
		}
		foreach ($this->fotoModel->find('subdir LIKE ?', array($oldDir . '%')) as $foto) {
			/** @var Foto $foto */
			$oldUUID = $foto->getUUID();
			// updaten gaat niet vanwege primary key
			$this->fotoModel->delete($foto);
			$foto->subdir = str_replace($oldDir, $newDir, $foto->subdir);
			$this->fotoModel->create($foto);
			foreach ($this->fotoTagsModel->find('refuuid = ?', array($oldUUID)) as $tag) {
				// updaten gaat niet vanwege primary key
				$this->fotoTagsModel->delete($tag);
				$tag->refuuid = $foto->getUUID();
				$this->fotoTagsModel->create($tag);
			}
		}
		if (false === @rmdir(PHOTOS_PATH . $oldDir)) {
			$error = error_get_last();
			setMelding($error['message'], -1);
		}
		return true;
	}

	public function setAlbumCover(FotoAlbum $album, Foto $cover) {
		$success = true;
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
					$this->fotoModel->delete($foto);
					$foto->filename = str_replace('folder', '', $foto->filename);
					$this->fotoModel->create($foto);
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
			$this->fotoModel->delete($cover);
			$cover->filename = substr_replace($cover->filename, 'folder', strrpos($cover->filename, '.'), 0);
			$this->fotoModel->create($cover);
		}
		return $success;
	}

	public function opschonen(FotoAlbum $fotoalbum) {
		foreach ($this->find('subdir LIKE ?', array($fotoalbum->subdir . '%')) as $album) {
			/** @var FotoAlbum $album */
			if (!$album->exists()) {
				foreach ($this->fotoModel->find('subdir LIKE ?', array($album->subdir . '%')) as $foto) {
					$this->fotoModel->delete($foto);
					$this->fotoTagsModel->verwijderFotoTags($foto);
				}
				$this->delete($album);
			}
		}
	}

}
