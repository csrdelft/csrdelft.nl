<?php

namespace CsrDelft\model\entity\fotoalbum;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\fotoalbum\FotoModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Foto.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Foto extends Afbeelding {
	const FOTOALBUM_ROOT = "/fotoalbum";
	const THUMBS_DIR = '_thumbs';
	const RESIZED_DIR = '_resized';

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
		'subdir' => array(T::StringKey),
		'filename' => array(T::StringKey),
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

	public function __construct($filename = null, FotoAlbum $album = null, $parse = false) {
		if ($filename === true) { // called from PersistenceModel
			$this->directory = join_paths(PHOTOALBUM_PATH, $this->subdir);
		} elseif ($album !== null) {
			$this->filename = $filename;
			$this->directory = $album->path;
			$this->subdir = $album->subdir;

			if (!path_valid(PHOTOALBUM_PATH, join_paths($album->subdir, $filename))) {
				throw new ResourceNotFoundException(); // Voorkom traversal door filename
			}
		}
		parent::__construct(null, $parse);
	}

	public function getUUID() {
		return join_paths($this->subdir, $this->filename) . '@' . get_class($this) . '.csrdelft.nl';
	}

	public function getThumbPath() {
		return join_paths(PHOTOALBUM_PATH, $this->subdir, self::THUMBS_DIR, $this->filename);
	}

	public function getResizedPath() {
		return join_paths(PHOTOALBUM_PATH, $this->subdir, self::RESIZED_DIR, $this->filename);
	}

	public function getAlbumUrl() {
		return direncode(join_paths(self::FOTOALBUM_ROOT, $this->subdir));
	}
	public function getAlbum() {
		return new FotoAlbum($this->directory);
	}
	public function getFullUrl() {
		return direncode(join_paths(self::FOTOALBUM_ROOT, $this->subdir, $this->filename));
	}

	public function getThumbUrl() {
		return direncode(join_paths(self::FOTOALBUM_ROOT, $this->subdir, self::THUMBS_DIR, $this->filename));
	}

	public function getResizedUrl() {
		return direncode(join_paths(self::FOTOALBUM_ROOT, $this->subdir, self::RESIZED_DIR, $this->filename));
	}

	public function hasThumb() {
		$path = $this->getThumbPath();
		return file_exists($path) && is_file($path);
	}

	public function hasResized() {
		$path = $this->getResizedPath();
		return file_exists($path) && is_file($path);
	}

	public function createThumb() {
		$path = join_paths(PHOTOALBUM_PATH, $this->subdir, self::THUMBS_DIR);
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command = IMAGEMAGICK . ' ' . escapeshellarg($this->getFullPath()) . ' -thumbnail 150x150^ -gravity center -extent 150x150 -format jpg -quality 80 -auto-orient ' . $rotate . escapeshellarg($this->getThumbPath());
		shell_exec($command);
		if ($this->hasThumb()) {
			chmod($this->getThumbPath(), 0644);
		} else {
			throw new CsrException('Thumb maken mislukt: ' . $this->getFullPath());
		}
	}

	public function createResized() {
		$path = join_paths(PHOTOALBUM_PATH, $this->subdir, self::RESIZED_DIR);
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command = IMAGEMAGICK . ' ' . escapeshellarg($this->getFullPath()) . ' -resize 1024x1024 -format jpg -quality 85 -interlace Line  -auto-orient ' . $rotate . escapeshellarg($this->getResizedPath());
		shell_exec($command);
		if ($this->hasResized()) {
			chmod($this->getResizedPath(), 0644);
		} else {
			throw new CsrException('Resized maken mislukt: ' . $this->getFullPath());
		}
	}

	public function isComplete() {
		return $this->hasThumb() && $this->hasResized();
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
