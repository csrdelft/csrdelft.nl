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

	public function __construct($path, $parse = true) {
		parent::__construct();
		if ($parse) {
			if ($this->exists()) {
				$this->filesize = @filesize($path);
				$image = @getimagesize($path);
				if ($image AND $this->filesize !== false) {
					$this->width = $image[0];
					$this->height = $image[1];
					$this->mimetype = $image['mime'];
				} else {
					throw new Exception('Afbeelding parsen mislukt: ' . $path);
				}
			} else {
				throw new Exception('Afbeelding niet leesbaar: ' . $path);
			}
		}
	}

}
