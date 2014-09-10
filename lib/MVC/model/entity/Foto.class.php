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

	public function __construct(FotoAlbum $album, $bestandsnaam) {
		parent::__construct($album->path . $bestandsnaam, false); // werkomheen traag fotoalbum: niet onnodig parsen
		$this->directory = $album;
		$this->filename = $bestandsnaam;
	}

	public function getPad() {
		return $this->directory->path . $this->filename;
	}

	public function getThumbPad() {
		return $this->directory->path . '_thumbs/' . $this->filename;
	}

	public function getResizedPad() {
		return $this->directory->path . '_resized/' . $this->filename;
	}

	public function getURL() {
		return CSR_PICS . '/' . direncode($this->directory->getSubDir() . $this->filename);
	}

	public function getThumbURL() {
		return CSR_PICS . '/' . direncode($this->directory->getSubDir() . '_thumbs/' . $this->filename);
	}

	public function getResizedURL() {
		return CSR_PICS . '/' . direncode($this->directory->getSubDir() . '_resized/' . $this->filename);
	}

	public function hasThumb() {
		$pad = $this->getThumbPad();
		return file_exists($pad) AND is_file($pad);
	}

	public function hasResized() {
		$pad = $this->getResizedPad();
		return file_exists($pad) AND is_file($pad);
	}

	public function maakThumb() {
		set_time_limit(0);
		$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($this->getPad()) . ' -thumbnail 150x150^^ -gravity center -extent 150x150 -format jpg -quality 80 ' . escapeshellarg($this->getThumbPad());
		$output = shell_exec($command) . '<hr />';
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output;
		}
		if ($this->hasThumb()) {
			chmod($this->getThumbPad(), 0644);
		} else {
			SimpleHTML::setMelding('Thumb maken mislukt voor: ' . $this->getThumbPad(), -1);
		}
	}

	public function maakResized() {
		set_time_limit(0);
		$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($this->getPad()) . ' -resize 1024x1024 -format jpg -quality 85 ' . escapeshellarg($this->getResizedPad());
		$output = shell_exec($command) . '<hr />';
		if (defined('RESIZE_OUTPUT')) {
			echo $command . '<br />';
			echo $output;
		}
		if ($this->hasResized()) {
			chmod($this->getResizedPad(), 0644);
		} else {
			SimpleHTML::setMelding('Resized maken mislukt voor: ' . $this->getResizedPad(), -1);
		}
	}

	public function isCompleet() {
		return ($this->hasThumb() && $this->hasResized());
	}

	/**
	 * Rotate resized & thumb for prettyPhoto to show the right way up.
	 * 
	 * @param float $degrees
	 */
	public function rotate($degrees) {
		$imagick = new Imagick();
		if ($this->hasResized()) {
			$imagick->readImage($this->getResizedPad());
			$imagick->rotateImage(new ImagickPixel('none'), $degrees);
			unlink($this->getResizedPad());
			$imagick->writeImage($this->getResizedPad());
			chmod($this->getResizedPad(), 644);
			$imagick->clear();
		}
		if ($this->hasThumb()) {
			$imagick->readImage($this->getThumbPad());
			$imagick->rotateImage(new ImagickPixel('none'), $degrees);
			unlink($this->getThumbPad());
			$imagick->writeImage($this->getThumbPad());
			chmod($this->getResizedPad(), 644);
			$imagick->clear();
		}
		$imagick->destroy();
	}

}
