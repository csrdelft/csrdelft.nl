<?php

/**
 * FotoAlbum.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
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
			setMelding('Album bestaat niet', -1);
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

	public function getUrl() {
		return CSR_ROOT . direncode(str_replace(PICS_PATH, '', $this->locatie));
	}

	public function getBreadcrumbs() {
		$locatie = str_replace(PICS_PATH, '', $this->locatie);
		$breadcrumbs = '';
		$mappen = array_filter(explode('/', $locatie));
		while (!empty($mappen)) {
			$mapnaam = array_pop($mappen);
			if (!empty($mappen)) {
				$subdir = implode('/', $mappen) . '/';
			} else {
				$subdir = '';
			}
			$breadcrumbs = ' » <a href="' . CSR_ROOT . '/' . $subdir . $mapnaam . '">' . ucfirst($mapnaam) . '</a>' . $breadcrumbs;
		}
		return substr($breadcrumbs, 3);
	}

	public function getFotos() {
		if (isset($this->fotos)) {
			return $this->fotos;
		}
		$this->fotos = array();
		foreach (glob($this->locatie . '*') as $path) {
			if (is_file($path)) {
				$parts = explode('/', $path);
				$bestandsnaam = end($parts);
				$foto = new Foto($this, $bestandsnaam);
				if ($foto->isCompleet()) {
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
			$mapnaam = end($parts);
			if (!startsWith($mapnaam, '_')) {
				$subalbum = new FotoAlbum($this, $mapnaam);
				$this->subalbums[] = $subalbum;
			}
		}
		$this->subalbums = array_reverse($this->subalbums);
		return $this->subalbums;
	}

	public function getThumbURL() {
		foreach ($this->getFotos() as $foto) {
			if (substr($foto->bestandsnaam, -10) == 'folder.jpg') {
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
			if ($subalbum->modified() > $recent->modified() AND FotoAlbumController::magBekijken($subalbum->locatie)) {
				$recent = $subalbum->getMostRecentSubAlbum();
			}
		}
		return $recent;
	}

	public function verwerkFotos() {
		if (!file_exists($this->locatie . '_thumbs')) {
			mkdir($this->locatie . '_thumbs');
			chmod($this->locatie . '_thumbs', 0755);
		}
		if (!file_exists($this->locatie . '_resized')) {
			mkdir($this->locatie . '_resized');
			chmod($this->locatie . '_resized', 0755);
		}
		foreach ($this->getFotos() as $foto) {
			if (!$foto->bestaatThumb()) {
				$foto->maakThumb();
			}
			if (!$foto->bestaatResized()) {
				$foto->maakResized();
			}
		}
		foreach ($this->getSubAlbums() as $subalbum) {
			$subalbum->verwerkFotos();
		}
	}

}
