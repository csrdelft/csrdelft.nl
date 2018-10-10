<?php

namespace CsrDelft\model\entity\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\fotoalbum\FotoModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;

/**
 * Foto.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Foto extends Afbeelding {

	/**
	 * Relatief pad in fotoalbum
	 * @var string
	 */
	public $subdir;
	/**
	 * Degrees of rotation
	 * @var int
	 */
	public $rotation;
	/**
	 * Uploader
	 * @var string
	 */
	public $owner;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'subdir' => array(T::String),
		'filename' => array(T::String),
		'rotation' => array(T::Integer),
		'owner' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('subdir', 'filename');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'fotos';

	/**
	 * Get Foto from filename (absolute path)
	 * @param $filename
	 */
	public static function fromFileName(string $filename, $parse = true) {
		$realfile = realpath($filename);
		if (!startsWith($realfile, realpath(PHOTOALBUM_PATH))) {
			return false;
		}
		return new Foto(basename($realfile), new FotoAlbum(dirname($realfile)), $parse);
	}
	public function __construct($filename = null, FotoAlbum $album = null, $parse = false) {
		if ($filename === true) { // called from PersistenceModel
			$this->directory = PHOTOALBUM_PATH . $this->subdir;
		} elseif ($album !== null) {
			$this->filename = $filename;
			$this->directory = $album->path;
			$this->subdir = $album->subdir;
		}
		parent::__construct(null, $parse);
	}

	public function getUUID() {
		return $this->subdir . $this->filename . '@' . get_class($this) . '.csrdelft.nl';
	}

	public function getAlbumPath() {
		return $this->directory;
	}


	public function getThumbPath() {
		return $this->directory . '_thumbs/' . $this->filename;
	}

	public function getResizedPath() {
		return $this->directory . '_resized/' . $this->filename;
	}

	public function getAlbumUrl() {
		return '/' . direncode($this->subdir);
	}
	public function getAlbum() {
		return new FotoAlbum($this->directory);
	}
	public function getFullUrl() {
		return '/plaetjes/' . direncode($this->subdir . $this->filename);
	}

	public function getThumbUrl() {
		return '/plaetjes/' . direncode($this->subdir . '_thumbs/' . $this->filename);
	}

	public function getResizedUrl() {
		return '/plaetjes/' . direncode($this->subdir . '_resized/' . $this->filename);
	}

	public function hasThumb() {
		$path = $this->getThumbPath();
		return file_exists($path) AND is_file($path);
	}

	public function hasResized() {
		$path = $this->getResizedPath();
		return file_exists($path) AND is_file($path);
	}

	public function createThumb() {
		$path = $this->directory . '_thumbs';
		if (!file_exists($path)) {
			mkdir($path);
			chmod($path, 0755);
		}
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command = IMAGEMAGICK . ' ' . escapeshellarg($this->getFullPath()) . ' -thumbnail 150x150^ -gravity center -extent 150x150 -format jpg -quality 80 -auto-orient ' . $rotate . escapeshellarg($this->getThumbPath());
		if (defined('RESIZE_OUTPUT')) {
			debugprint($command);
		}
		$output = shell_exec($command);
		if (defined('RESIZE_OUTPUT')) {
			debugprint($output);
		}
		if ($this->hasThumb()) {
			chmod($this->getThumbPath(), 0644);
		} else {
			throw new CsrException('Thumb maken mislukt: ' . $this->getFullPath());
		}
	}

	public function createResized() {
		$path = $this->directory . '_resized';
		if (!file_exists($path)) {
			mkdir($path);
			chmod($path, 0755);
		}
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command = IMAGEMAGICK . ' ' . escapeshellarg($this->getFullPath()) . ' -resize 1024x1024 -format jpg -quality 85 -interlace Line  -auto-orient ' . $rotate . escapeshellarg($this->getResizedPath());
		if (defined('RESIZE_OUTPUT')) {
			debugprint($command);
		}
		$output = shell_exec($command);
		if (defined('RESIZE_OUTPUT')) {
			debugprint($output);
		}
		if ($this->hasResized()) {
			chmod($this->getResizedPath(), 0644);
		} else {
			throw new CsrException('Resized maken mislukt: ' . $this->getFullPath());
		}
	}

	public function isComplete() {
		return $this->hasThumb() AND $this->hasResized();
	}

	/**
	 * Rotate resized & thumb for prettyPhoto to show the right way up.
	 *
	 * @param int $degrees
	 */
	public function rotate($degrees) {
		if (!isset($this->rotation)) {
			FotoModel::instance()->retrieveAttributes($this, array('rotation', 'owner'));
		}
		$this->rotation += $degrees;
		$this->rotation %= 360;
		FotoModel::instance()->update($this);
		if ($this->hasThumb()) {
			unlink($this->getThumbPath());
		}
		$this->createThumb();
		if ($this->hasResized()) {
			unlink($this->getResizedPath());
		}
		$this->createResized();
	}

	public function isOwner() {
		if (!isset($this->owner)) {
			FotoModel::instance()->retrieveAttributes($this, array('rotation', 'owner'));
		}
		return LoginModel::mag($this->owner);
	}

	public function magBekijken() {
		return $this->getAlbum()->magBekijken();
	}

}
