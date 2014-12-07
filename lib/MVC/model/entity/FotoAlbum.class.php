<?php

require_once 'MVC/model/entity/Foto.class.php';

/**
 * FotoAlbum.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoAlbum extends Map {

	/**
	 * Als deze regexp matched is het album alleen voor leden toegankelijk
	 * @var string
	 */
	private static $alleenLeden = '/(intern|novitiaat|ontvoering|feuten|slachten|zuipen|prive|privé|Posters)/i';
	/**
	 * Relatief pad in fotoalbum
	 * @var string 
	 */
	public $subdir;
	/**
	 * Subalbums in dit album
	 * @var FotoAlbum[]
	 */
	private $subalbums;
	/**
	 * Fotos in dit album
	 * @var Foto[]
	 */
	private $fotos;
	/**
	 * Fotos zonder thumb of resized
	 * @var Foto[]
	 */
	private $fotos_incompleet;
	/**
	 * Creator
	 * @var Lid
	 */
	public $owner;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'subdir' => array(T::String),
		'owner'	 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('subdir');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'fotoalbums';

	public function __construct($path = null) {
		parent::__construct();
		if ($path === true) { // called from PersistenceModel
			$this->path = PICS_PATH . $this->subdir;
		} else {
			if (!endsWith($path, '/')) {
				$path .= '/';
			}
			$this->path = $path;
			$this->subdir = str_replace(PICS_PATH, '', $this->path);
		}
		$this->dirname = basename($this->path);
	}

	/**
	 * File modification time van het album.
	 */
	public function modified() {
		return filemtime($this->path);
	}

	public function getParentName() {
		return ucfirst(basename(dirname($this->subdir)));
	}

	public function getUrl() {
		return CSR_ROOT . '/' . direncode($this->subdir);
	}

	public function isEmpty() {
		$subalbums = $this->getSubAlbums();
		return empty($subalbums) AND ! $this->hasFotos(true);
	}

	public function hasFotos($incompleet = false) {
		$fotos = $this->getFotos($incompleet);
		return !empty($fotos);
	}

	public function getFotos($incompleet = false) {
		if (!isset($this->fotos)) {

			$this->fotos = array();
			$this->fotos_incompleet = array();

			$handle = opendir($this->path);
			if (!$handle) {
				return false;
			}
			while (false !== ($entry = readdir($handle))) {
				if (is_file($this->path . $entry)) {
					$foto = new Foto($entry, $this);
					if ($foto->isComplete()) {
						$this->fotos[] = $foto;
					} else {
						$this->fotos_incompleet[] = $foto;
					}
				}
			}
			closedir($handle);
		}
		if ($incompleet) {
			return array_merge($this->fotos, $this->fotos_incompleet);
		} else {
			return $this->fotos;
		}
	}

	public function getSubAlbums() {
		if (!isset($this->subalbums)) {

			$this->subalbums = array();

			$scan = scandir($this->path, SCANDIR_SORT_DESCENDING);
			if (empty($scan)) {
				return false;
			}
			foreach ($scan as $entry) {
				if ($entry !== '.' AND $entry != '..' AND is_dir($this->path . $entry)) {
					$subalbum = FotoAlbumModel::instance()->getFotoAlbum($this->path . $entry);
					if ($subalbum) {
						$this->subalbums[] = $subalbum;
					}
				}
			}
		}
		return $this->subalbums;
	}

	public function getCoverUrl($thumb = true) {
		foreach ($this->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				if ($thumb) {
					return $foto->getThumbUrl();
				} else {
					return $foto->getResizedUrl();
				}
			}
		}
		// Anders gewoon de eerste:
		if (isset($this->fotos[0])) {
			if ($thumb) {
				return $this->fotos[0]->getThumbUrl();
			} else {
				return $this->fotos[0]->getResizedUrl();
			}
		}
		// Foto uit subalbum:
		foreach ($this->getSubAlbums() as $album) {
			return $album->getCoverUrl();
		}
		// If all else fails:
		return CSR_PICS . '/_geen_thumb.jpg';
	}

	public function getMostRecentSubAlbum() {
		$recent = $this;
		foreach ($this->getSubAlbums() as $subalbum) {
			if ($subalbum->modified() > $recent->modified()) {
				$recent = $subalbum->getMostRecentSubAlbum();
			}
		}
		return $recent;
	}

	public function magBekijken() {
		if (!startsWith($this->path, PICS_PATH . 'fotoalbum/')) {
			return false;
		}
		if (LoginModel::mag('P_LEDEN_READ')) {
			return true;
		} else {
			if (preg_match(self::$alleenLeden, $this->path)) {
				return false; // Deze foto's alleen voor leden
			}
			return true;
		}
	}

	public function isOwner() {
		if (!isset($this->owner)) {
			$this->owner = FotoAlbumModel::instance()->retrieveAttributes($this, array('owner'));
		}
		return LoginModel::mag($this->owner);
	}

}
