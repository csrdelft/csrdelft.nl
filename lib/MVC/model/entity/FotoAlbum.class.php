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
	 * Fotos in dit album
	 * @var Foto[]
	 */
	private $fotos;
	/**
	 * Subalbums in dit album
	 * @var FotoAlbum[]
	 */
	private $subalbums;
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
		'directory'	 => array(T::String),
		'owner'		 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('directory');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'fotoalbums';

	public function __construct($locatie = null) {
		parent::__construct();
		$this->path = $locatie;
		$this->dirname = basename($locatie);
		if (!$this->exists()) {
			$this->fotos = array();
			$this->subalbums = array();
		}
	}

	/**
	 * Bestaat er een map met de naam van het pad.
	 */
	public function exists() {
		return @is_readable($this->path) && file_exists($this->path) && is_dir($this->path);
	}

	/**
	 * File modification time van het album.
	 */
	public function modified() {
		return filemtime($this->path);
	}

	public function getSubDir() {
		return str_replace(PICS_PATH, '', $this->path);
	}

	public function getUrl() {
		return CSR_ROOT . '/' . direncode($this->getSubDir());
	}

	public function getFotos($incompleet = false) {
		if (isset($this->fotos)) {
			return $this->fotos;
		}
		$this->fotos = array();
		$glob = glob($this->path . '*');
		if (!is_array($glob)) {
			return array();
		}
		foreach ($glob as $path) {
			if (is_file($path)) {
				$filename = basename($path);
				$foto = new Foto($this, $filename);
				if ($incompleet OR $foto->isComplete()) {
					$this->fotos[] = $foto;
				}
			}
		}
		return $this->fotos;
	}

	public function getSubAlbums() {
		if (isset($this->subalbums)) {
			return $this->subalbums;
		}
		$this->subalbums = array();
		$glob = glob($this->path . '*', GLOB_ONLYDIR);
		if (!is_array($glob)) {
			return array();
		}
		foreach ($glob as $path) {
			$subalbum = FotoAlbumModel::instance()->getFotoAlbum($path);
			if ($subalbum) {
				$this->subalbums[] = $subalbum;
			}
		}
		$this->subalbums = array_reverse($this->subalbums);
		return $this->subalbums;
	}

	public function getThumbURL() {
		foreach ($this->getFotos() as $foto) {
			if (strpos($foto->filename, 'folder') !== false) {
				return $foto->getThumbUrl();
			}
		}
		// Anders gewoon de eerste:
		if (isset($this->fotos[0])) {
			return $this->fotos[0]->getThumbUrl();
		}
		// Foto uit subalbum:
		foreach ($this->getSubAlbums() as $album) {
			return $album->getThumbURL();
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
		if (startsWith($this->dirname, '_') OR ! startsWith($this->path, PICS_PATH . 'fotoalbum/')) {
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
