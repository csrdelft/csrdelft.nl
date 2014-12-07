<?php

require_once 'MVC/model/entity/Bestand.class.php';

/**
 * Afbeelding.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Afbeelding extends Bestand {

	/**
	 * Geaccepteerde afbeelding types
	 * @var array
	 */
	public static $mimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/tiff');
	/**
	 * Breedte in pixels
	 * @var int
	 */
	public $width;
	/**
	 * Hoogte in pixels
	 * @var int
	 */
	public $height;

	public function __construct($path = null, $parse = true) {
		parent::__construct();
		if ($path !== null) {
			$this->directory = dirname($path) . '/';
			$this->filename = basename($path);
		}
		if ($parse) {
			if ($this->exists()) {
				$this->filesize = @filesize($this->directory . $this->filename);
				$image = @getimagesize($this->directory . $this->filename);
				if ($image AND $this->filesize !== false) {
					$this->width = $image[0];
					$this->height = $image[1];
					$this->mimetype = $image['mime'];
				} else {
					throw new Exception('Afbeelding parsen mislukt: ' . $this->directory . $this->filename);
				}
			} else {
				throw new Exception('Afbeelding niet leesbaar: ' . $this->directory . $this->filename);
			}
		}
	}

}
