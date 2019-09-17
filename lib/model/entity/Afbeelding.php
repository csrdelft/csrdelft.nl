<?php

namespace CsrDelft\model\entity;

use CsrDelft\common\CsrGebruikerException;

/**
 * Afbeelding.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class Afbeelding extends Bestand {

	/**
	 * Geaccepteerde afbeelding types
	 * @var array
	 */
	public static $mimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/tiff', 'image/x-ms-bmp');
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
	 * @return string
	 */
	public function getFullPath() {
		return join_paths($this->directory, $this->filename);
	}

	/**
	 * Constructor is called late (after attributes are set)
	 * by PDO::FETCH_CLASS with $cast = true
	 *
	 * @param string $path
	 * @param bool $parse
	 *
	 * @throws CsrGebruikerException
	 */
	public function __construct($path, $parse = true) {
		parent::__construct();
		if ($path !== null) {
			$this->directory = dirname($path) . '/';
			$this->filename = basename($path);
		}
		if ($parse) {
			$this->filesize = @filesize($this->getFullPath());
			if (!$this->filesize) {
				throw new CsrGebruikerException('Afbeelding is leeg of bestaat niet: ' . $this->filename);
			}
			$image = @getimagesize($this->getFullPath());
			if (!$image) {
				throw new CsrGebruikerException('Afbeelding is geen afbeelding: ' . $this->filename);
			}
			$this->width = $image[0];
			$this->height = $image[1];
			$this->mimetype = $image['mime'];

			if (!in_array($this->mimetype, static::$mimeTypes)) {
				throw new CsrGebruikerException('Geen afbeelding: [' . $this->mimetype . '] ' . $this->filename);
			}
		}
	}

	/**
	 * Serve this image with the correct http headers.
	 */
	public function serve() {
		$file = $this->getFullPath();
		$lastModified = filemtime($file);
		$etagFile = md5_file(__FILE__);

		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etagFile) {
			header('HTTP/1.1 304 Not Modified');
			exit;
		}

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) {
			header('HTTP/1.1 304 Not Modified');
			exit;
		}

		header("Content-type: " . image_type_to_mime_type(exif_imagetype($file)));
		header('Content-Length: ' . filesize($file));
		header('ETag: ' . $etagFile);
		header('Last-Modified: ' . gmdate(\DateTime::RFC7231, $lastModified));
		header('Pragma: public');
		header('Cache-Control: max-age=2592000, public');
		header('Expires: ' . gmdate(\DateTime::RFC7231, time() + 2592000));
		readfile($file);
		exit;
	}

	public function download() {
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $this->mimetype);
		header('Content-Disposition: attachment; filename="' . $this->filename . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $this->filesize);
		readfile($this->getFullPath());
		exit;
	}

}
