<?php

namespace CsrDelft\model\entity\fotoalbum;

use CsrDelft\model\entity\Map;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;

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
	protected $subalbums;
	/**
	 * Fotos in dit album
	 * @var Foto[]
	 */
	protected $fotos;
	/**
	 * Fotos zonder thumb of resized
	 * @var Foto[]
	 */
	protected $fotos_incompleet;
	/**
	 * Creator
	 * @var string
	 */
	public $owner;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'subdir' => array(T::StringKey),
		'owner' => array(T::UID)
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
			$this->path = realpathunix(PHOTOALBUM_PATH . $this->subdir);
		} else {
			$path = realpathunix($path);
			if (!endsWith($path, '/')) {
				$path .= '/';
			}
			$this->path = $path;
			//We verwijderen het beginstuk van de string
			$prefix = realpathunix(PHOTOALBUM_PATH) . "/";
			$this->subdir = substr($this->path, strlen($prefix));
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
		return '/' . direncode($this->subdir);
	}

	public function isEmpty() {
		$subalbums = $this->getSubAlbums();
		return empty($subalbums) AND !$this->hasFotos(true);
	}

	public function hasFotos($incompleet = false) {
		$fotos = $this->getFotos($incompleet);
		return !empty($fotos);
	}

	public function getFotos($incompleet = false) {
		if (!isset($this->fotos)) {

			$this->fotos = array();
			$this->fotos_incompleet = array();

			$scan = scandir($this->path, SCANDIR_SORT_ASCENDING);
			if (empty($scan)) {
				return false;
			}
			foreach ($scan as $entry) {
				if (is_file($this->path . $entry)) {
					$foto = new Foto($entry, $this);
					if ($foto->isComplete()) {
						$this->fotos[] = $foto;
					} else {
						$this->fotos_incompleet[] = $foto;
					}
				}
			}
		}
		if ($incompleet) {
			return array_merge($this->fotos, $this->fotos_incompleet);
		} else {
			return $this->fotos;
		}
	}

	public function orderByDateModified() {
		$order = array();
		foreach ($this->getFotos() as $i => $foto) {
			$order[$i] = filemtime($foto->getFullPath());
		}
		arsort($order);
		$result = array();
		foreach ($order as $i => $mtime) {
			$result[] = $this->fotos[$i];
		}
		$this->fotos = $result;
	}

	public function getSubAlbums($recursive = false) {
		if (!isset($this->subalbums)) {

			$this->subalbums = array();

			$scan = scandir($this->path, SCANDIR_SORT_DESCENDING);
			if (empty($scan)) {
				return false;
			}
			foreach ($scan as $entry) {
				if (substr($entry, 0, 1) !== '.' AND is_dir($this->path . $entry)) {
					$subalbum = FotoAlbumModel::instance()->getFotoAlbum($this->path . $entry);
					if ($subalbum) {
						$this->subalbums[] = $subalbum;
						if ($recursive) {
							$subalbum->getSubalbums(true);
						}
					}
				}
			}
		}
		return $this->subalbums;
	}

	public function getCoverUrl() {
		if ($this->hasFotos()) {
			if ($this->dirname !== 'Posters') {
				foreach ($this->getFotos() as $foto) {
					if (strpos($foto->filename, 'folder') !== false) {
						return $foto->getThumbUrl();
					}
				}
			}
			// Anders een willekeurige foto:
			$count = count($this->fotos);
			if ($count > 0) {
				$idx = rand(0, $count - 1);
				return $this->fotos[$idx]->getThumbUrl();
			}
		}
		// Foto uit willekeurig subalbum:
		$count = count($this->getSubAlbums());
		if ($count > 0) {
			$idx = rand(0, $count - 1);
			return $this->subalbums[$idx]->getCoverUrl();
		}
		// If all else fails:
		return '/plaetjes/_geen_thumb.jpg';
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

	/**
	 * Zegt of dit album publiek toegankelijk is.
	 * @return bool
	 */
	public function isPubliek() {
		return preg_match('/^fotoalbum\/Publiek\/.*$/', $this->subdir) == 1;
	}
	public function magBekijken() {
		if (!startsWith(realpath($this->path), realpath(PHOTOALBUM_PATH . 'fotoalbum/'))) {
			return false;
		}
		if ($this->isPubliek()) {
			return LoginModel::mag('P_ALBUM_PUBLIC_READ');
		} else {
			return LoginModel::mag('P_ALBUM_READ');
		}
	}

	public function isOwner() {
		if (!isset($this->owner)) {
			FotoAlbumModel::instance()->retrieveAttributes($this, array('owner'));
		}
		return LoginModel::mag($this->owner);
	}

	/**
	 * Maak een object voor jGallery.
	 *
	 * @return string[][]
	 */
	public function getAlbumArrayRecursive() {
		$fotos = [];
		foreach ($this->getFotos() as $foto) {
			$fotos[] = [
				'url' => $foto->getResizedUrl(),
				'thumbUrl' => $foto->getThumbUrl(),
				'title' => CSR_ROOT . str_replace('%20', ' ', $foto->getFullUrl()),
			];
		}

		$hoofdAlbum = [
			'title' => ucfirst($this->dirname),
			'images' => $fotos,
		];

		$albums = [$hoofdAlbum];

		foreach ($this->getSubAlbums() as $subAlbum) {
			if ($subAlbum->hasFotos()) {
				$albums = array_merge($albums, $subAlbum->getAlbumArrayRecursive());
			}
		}

		return $albums;
	}

	/**
	 * Album array zonder poespas. Wordt voor sliders gebruikt.
	 *
	 * @return string[][]
	 */
	public function getAlbumArray() {
		$fotos = [];

		foreach ($this->getFotos() as $foto) {
			$fotos[] = [
				'url' => $foto->getResizedUrl(),
			];
		}

		return $fotos;
	}
	public function magVerwijderen() {
		if($this->isOwner()) {
			return true;
		}
		if($this->isPubliek()) {
			return LoginModel::mag('P_ALBUM_PUBLIC_DEL');
		}
		else{
			return LoginModel::mag('P_ALBUM_DEL');
		}
	}

	public function magToevoegen() {
		if($this->isPubliek()) {
			return LoginModel::mag('P_ALBUM_PUBLIC_ADD');
		}
		else{
			return LoginModel::mag('P_ALBUM_ADD');
		}
	}

	public function magAanpassen() {
		if($this->isPubliek()) {
			return LoginModel::mag('P_ALBUM_PUBLIC_MOD');
		}
		else{
			return LoginModel::mag('P_ALBUM_MOD') || $this->isOwner();
		}
	}

	public function magDownloaden() {
		if($this->isPubliek()) {
			return LoginModel::mag('P_ALBUM_PUBLIC_DOWN');
		}
		else{
			return LoginModel::mag('P_ALBUM_DOWN');
		}
	}

}
