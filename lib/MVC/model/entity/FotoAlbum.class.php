<?php

/**
 * FotoAlbum.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FotoAlbum extends Map {

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

	public function FotoAlbum(Map $parent, $mapnaam) {
		$this->locatie = $parent->locatie . $mapnaam . '/';
		$this->mapnaam = $mapnaam;
		if (!$this->exists()) {
			$this->fotos = array();
			$this->subalbums = array();
		}
	}

	/**
	 * Bestaat er een map met de naam van het pad.
	 */
	public function exists() {
		return file_exists($this->locatie) && is_dir($this->locatie);
	}

	/**
	 * File modification time van het album.
	 */
	public function modified() {
		return filemtime($this->locatie);
	}

	public function getSubDir() {
		return str_replace(PICS_PATH, '', $this->locatie);
	}

	public function getUrl() {
		return CSR_ROOT . direncode($this->getSubDir());
	}

	public function getFotos($incompleet = false) {
		if (isset($this->fotos)) {
			return $this->fotos;
		}
		$this->fotos = array();
		foreach (glob($this->locatie . '*') as $path) {
			if (is_file($path)) {
				$parts = explode('/', $path);
				$bestandsnaam = end($parts);
				$foto = new Foto($this, $bestandsnaam);
				if ($incompleet OR $foto->isCompleet()) {
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
		foreach (glob($this->locatie . '*', GLOB_ONLYDIR) as $path) {
			$parts = explode('/', $path);
			$naam = end($parts);
			if (!startsWith($naam, '_')) {
				$subalbum = FotoAlbumModel::getFotoAlbum($this, $naam);
				if ($subalbum) {
					$this->subalbums[] = $subalbum;
				}
			}
		}
		$this->subalbums = array_reverse($this->subalbums);
		return $this->subalbums;
	}

	public function getThumbURL() {
		foreach ($this->getFotos() as $foto) {
			if (strpos($foto->bestandsnaam, 'folder')) {
				return $foto->getThumbURL();
			}
		}
		// Anders gewoon de eerste:
		if (isset($this->fotos[0])) {
			return $this->fotos[0]->getThumbURL();
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

}
