<?php

require_once 'MVC/model/entity/FotoAlbum.class.php';

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
	 * @var Lid
	 */
	public $owner;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'subdir'	 => array(T::String),
		'filename'	 => array(T::String),
		'rotation'	 => array(T::Integer),
		'owner'		 => array(T::UID)
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

	public function __construct(FotoAlbum $album = null, $filename = null) {
		// werkomheen traag fotoalbum: niet onnodig parsen
		//parent::__construct($album->path . $bestandsnaam, false);
		if ($album !== null) {
			$this->filename = $filename;
			$this->directory = $album->path;
			$this->subdir = $album->subdir;
		}
	}

	/**
	 * Bestaat er een bestand met de naam en het pad.
	 */
	public function exists() {
		return @is_readable(PICS_PATH . $this->subdir . $this->filename) AND is_file(PICS_PATH . $this->subdir . $this->filename);
	}

	public function getAlbumPath() {
		return $this->directory;
	}

	public function getFullPath() {
		return $this->directory . $this->filename;
	}

	public function getThumbPath() {
		return $this->directory . '_thumbs/' . $this->filename;
	}

	public function getResizedPath() {
		return $this->directory . '_resized/' . $this->filename;
	}

	public function getAlbumUrl() {
		return CSR_ROOT . '/' . direncode($this->subdir);
	}

	public function getFullUrl() {
		return CSR_PICS . '/' . direncode($this->subdir . $this->filename);
	}

	public function getThumbUrl() {
		return CSR_PICS . '/' . direncode($this->subdir . '_thumbs/' . $this->filename);
	}

	public function getResizedUrl() {
		return CSR_PICS . '/' . direncode($this->subdir . '_resized/' . $this->filename);
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
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($this->getFullPath()) . ' -thumbnail 150x150^^ -gravity center -extent 150x150 -format jpg -quality 80 ' . $rotate . escapeshellarg($this->getThumbPath());
		$output = shell_exec($command);
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output . '<hr />';
		}
		if ($this->hasThumb()) {
			chmod($this->getThumbPath(), 0644);
		} else {
			throw new Exception('Thumb maken mislukt: ' . $command . '<br />' . $output);
		}
	}

	public function createResized() {
		if (empty($this->rotation)) {
			$rotate = '';
		} else {
			$rotate = '-rotate ' . $this->rotation . ' ';
		}
		$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($this->getFullPath()) . ' -resize 1024x1024 -format jpg -quality 85 ' . $rotate . escapeshellarg($this->getResizedPath());
		$output = shell_exec($command);
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output . '<hr />';
		}
		if ($this->hasResized()) {
			chmod($this->getResizedPath(), 0644);
		} else {
			throw new Exception('Resized maken mislukt: ' . $command . '<br />' . $output);
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
			$attr = array('rotation', 'owner');
			FotoModel::instance()->retrieveAttributes($this, $attr);
			$this->castValues($attr);
		}
		$this->rotation += $degrees;
		$this->rotation %= 360;
		FotoModel::instance()->update($this);
		if ($this->hasResized()) {
			unlink($this->getResizedPath());
		}
		$this->createResized();
		if ($this->hasThumb()) {
			unlink($this->getThumbPath());
		}
		$this->createThumb();
	}

	public function isOwner() {
		if (!isset($this->owner)) {
			$attr = array('rotation', 'owner');
			FotoModel::instance()->retrieveAttributes($this, $attr);
			$this->castValues($attr);
		}
		return LoginModel::mag($this->owner);
	}

}
