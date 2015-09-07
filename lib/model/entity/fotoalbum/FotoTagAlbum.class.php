<?php

/**
 * FotoTagAlbum.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoTagAlbum extends FotoAlbum {

	public $uid;

	public function __construct($uid) {
		// no parent constructor
		$this->uid = $uid;
		$this->subalbums = array();
		$this->path = PICS_PATH;
		$this->dirname = 'Foto\'s met ' . ProfielModel::getNaam($uid, 'civitas');
	}

	public function modified() {
		return time();
	}

	public function getParentName() {
		return null;
	}

	public function getUrl() {
		return '/fotoalbum/' . $this->uid;
	}

	public function exists() {
		return true;
	}

	public function isEmpty() {
		return false;
	}

	public function hasFotos($incompleet = false) {
		return true;
	}

	public function getFotos($incompleet = false) {
		if (!isset($this->fotos)) {
			// find tagged fotos
			foreach (FotoTagsModel::instance()->find('keyword = ?', array($this->uid)) as $tag) {
				$foto = FotoModel::getUUID($tag->refuuid);
				if ($foto) {
					$this->fotos[] = $foto;
				}
			}
		}
		return $this->fotos;
	}

	public function magBekijken() {
		return LoginModel::mag('P_LEDEN_READ');
	}

	public function isOwner() {
		return $this->uid === LoginModel::getUid();
	}

}
