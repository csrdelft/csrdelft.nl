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
	 * Breedte in pixels
	 * @var int
	 */
	public $width;
	/**
	 * Hoogte in pixels
	 * @var int
	 */
	public $height;
	/**
	 * Mime-types van afbeeldingen
	 * @var array
	 */
	public static $mimeTypes = array('image/png', 'image/jpeg', 'image/gif');

	public function __construct($path = '', $parse = true) {
		parent::__construct();
		if ($parse) {
			$image = getimagesize($path); // suppress warnings
			$this->width = $image[0];
			$this->height = $image[1];
			$this->mimetype = $image['mime'];
		}
	}

}
