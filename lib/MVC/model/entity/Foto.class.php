<?php

require_once 'MVC/model/entity/FotoAlbum.class.php';

/**
 * Foto.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 */
class Foto extends Bestand {

	public function __construct(FotoAlbum $album, $bestandsnaam) {
		$this->map = $album;
		$this->bestandsnaam = $bestandsnaam;
	}

	public function getPad() {
		return $this->map->locatie . $this->bestandsnaam;
	}

	public function getThumbPad() {
		return $this->map->locatie . '_thumbs/' . $this->bestandsnaam;
	}

	public function getResizedPad() {
		return $this->map->locatie . '_resized/' . $this->bestandsnaam;
	}

	public function getThumbURL() {
		return CSR_PICS . direncode(str_replace(PICS_PATH, '', $this->map->locatie) . '_thumbs/' . $this->bestandsnaam);
	}

	public function getResizedURL() {
		return CSR_PICS . direncode(str_replace(PICS_PATH, '', $this->map->locatie) . '_resized/' . $this->bestandsnaam);
	}

	public function bestaatThumb() {
		return file_exists($this->getThumbPad()) AND is_file($this->getThumbPad());
	}

	public function bestaatResized() {
		return file_exists($this->getResizedPad()) AND is_file($this->getResizedPad());
	}

	public function maakThumb() {
		set_time_limit(0);
		$command = IMAGEMAGICK_PATH . ' ' . escapeshellarg($this->getPad()) . ' -thumbnail 150x150^^ -gravity center -extent 150x150 -format jpg -quality 80 ' . escapeshellarg($this->getThumbPad());
		$output = shell_exec($command) . '<hr />';
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output;
		}
		chmod($this->getThumbPad(), 0644);
	}

	public function maakResized() {
		set_time_limit(0);
		$command = IMAGEMAGICK_PATH . ' ' . escapeshellarg($this->getPad()) . ' -resize 1024x1024 -format jpg -quality 85 ' . escapeshellarg($this->getResizedPad());
		$output = shell_exec($command) . '<hr />';
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output;
		}
		chmod($this->getResizedPad(), 0644);
	}

	public function isCompleet() {
		return ($this->bestaatThumb() && $this->bestaatResized());
	}

}
