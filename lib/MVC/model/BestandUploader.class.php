<?php

/**
 * BestandUploader.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Verschillende manieren om bestanden te uploaden.
 */
abstract class BestandUploader implements Validator {

	/**
	 * Error(s) after validate
	 * @var string
	 */
	protected $error;
	/**
	 * Bestand
	 * @var Bestand
	 */
	private $bestand;

	public function __construct() {
		
	}

	/**
	 * Is deze uploadmethode beschikbaar?
	 * 
	 * @return boolean
	 */
	public abstract function isAvailable();

	/**
	 * Bestand uiteindelijk opslaan op de juiste plek.
	 * 
	 * @param string $destination fully qualified path with trailing slash
	 * @param string $filename filename with extension
	 */
	abstract public function verplaatsBestand($destination, $filename);

	public function getBestand() {
		return $this->bestand;
	}

	public function createBestand($naam, $size, $type = null, $tmp = null) {
		$this->bestand = new Bestand();
		$this->bestand->bestandsnaam = $naam;
		$this->bestand->size = $size;
		if ($type !== null) {
			$this->bestand->mimetype = $type;
		} elseif ($tmp !== null) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$this->bestand->mimetype = finfo_file($finfo, $tmp);
			finfo_close($finfo);
		}
	}

	public function getError() {
		return $this->error;
	}

	protected function addError($error) {
		$this->error .= $error . "\n";
	}

}

class BestandBehouden extends BestandUploader {

	public function __construct($bestaat, $naam, $size) {
		parent::__construct();
		if ($bestaat) {
			$this->createBestand($naam, $size);
		}
	}

	public function isAvailable() {
		return (boolean) $this->getBestand();
	}

	public function validate() {
		return true;
	}

	public function verplaatsBestand($destination, $filename) {
		return file_exists($destination . $filename);
	}

}

class UploadHttp extends BestandUploader {

	protected $tmp_name;

	public function isAvailable() {
		return true;
	}

	public function validate() {
		if (!isset($_FILES['bestand'])) {
			$this->addError('Formulier niet compleet');
		}
		$upload = $_FILES['bestand'];
		if ($upload['error'] != 0) {
			switch ($upload['error']) {
				case 1:
					$this->addError('Bestand is te groot: Maximaal ' . ini_get('upload_max_filesize') . 'B ');
					break;
				case 4:
					$this->addError('Selecteer een bestand');
					break;
				default:
					$this->addError('Upload-error: error-code: ' . $upload['error']);
			}
		}
		if ($this->getError() == '') {
			$this->tmp_name = $upload['tmp_name'];
			$this->createBestand($upload['name'], $upload['size'], $upload['type']);
			return true;
		} else {
			return false;
		}
	}

	public function verplaatsBestand($destination, $filename) {
		if (!is_writable($destination)) {
			throw new Exception('Doelmap is niet beschrijfbaar');
		}
		if (is_uploaded_file($this->tmp_name)) {
			return move_uploaded_file($this->tmp_name, $destination . $filename);
		}
		return false;
	}

}

/**
 * UploadUrl
 *
 * Kan een bestand downloaden van een url, met file_get_contents of de
 * cURL-extensie. Als beide niet beschikbaar zijn wordt het formulier-
 * element niet weergegeven.
 */
class UploadUrl extends BestandUploader {

	protected $url = 'http://';
	protected $file_contents;

	public function getUrl() {
		return $this->url;
	}

	public function isAvailable() {
		return $this->file_get_contents_available() OR function_exists('curl_init');
	}

	protected function file_get_contents_available() {
		return in_array(ini_get('allow_url_fopen'), array('On', 'Yes', 1));
	}

	protected function curl_file_get_contents($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		return curl_exec($ch);
	}

	protected function file_get_contents($url) {
		if ($this->file_get_contents_available()) {
			return file_get_contents($url);
		} else {
			return $this->curl_file_get_contents($url);
		}
	}

	public function validate() {
		if (!$this->isAvailable()) {
			$this->addError('PHP.ini configuratie: cURL of allow_url_fopen moet aan staan.');
		}
		if (!isset($_POST['url'])) {
			$this->addError('Formulier niet compleet.');
		}
		if (!url_like(urldecode($_POST['url']))) {
			$this->addError('Ongeldige url.');
		}
		$this->url = $_POST['url'];
		if ($this->getError() == '') {
			$this->file_contents = $this->file_get_contents($this->url);
			if (empty($this->file_contents)) {
				$this->addError('Bestand is leeg, check de url.');
			} else {
				$naam = substr(trim($this->url), strrpos($this->url, '/') + 1);
				$naam = preg_replace("/[^a-zA-Z0-9\s\.\-\_]/", '', $naam);
				//Bestand tijdelijk omslaan om mime-type te bepalen.
				$tmp_bestand = TMP_PATH . '/BestandUploader' . LoginLid::instance()->getUid() . microtime() . '.tmp';
				if (is_writable(TMP_PATH)) {
					$size = file_put_contents($tmp_bestand, $this->file_contents);
					$this->createBestand($naam, $size, null, $tmp_bestand);
				} else {
					$this->addError('Ophalen vanaf url mislukt: TMP_PATH is niet beschrijfbaar.');
				}
			}
		}
		return $this->getError() == '';
	}

	public function verplaatsBestand($destination, $filename) {
		if (!is_writable($destination)) {
			throw new Exception('Doelmap is niet beschrijfbaar');
		}
		return file_put_contents($destination . $filename, $this->file_contents);
	}

}

class UploadFtp extends BestandUploader {

	/**
	 * Lijst van bestanden in de publieke ftp map
	 * @var array
	 */
	protected $file_list;
	/**
	 * Volledig pad naar bestand
	 * @var string
	 */
	protected $path;
	/**
	 * Pad binnen de publieke ftp map
	 * @var string
	 */
	protected $subdir;

	public function __construct($subdir = '') {
		parent::__construct();
		$this->setSubDir($subdir);
	}

	public function isAvailable() {
		return file_exists($this->path);
	}

	public function getFilelist() {
		if (!$this->file_list) {
			$this->file_list = array();
			$handler = opendir($this->path);
			while ($file = readdir($handler)) {
				// We willen geen directories en geen verborgen bestanden.
				if (!is_dir($this->path . $file) AND substr($file, 0, 1) != '.') {
					$this->file_list[] = $file;
				}
			}
			closedir($handler);
		}
		return $this->file_list;
	}

	public function getSubDir() {
		return $this->subdir;
	}

	public function setSubDir($subdir) {
		if (file_exists(PUBLIC_FTP . $subdir)) {
			$this->subdir = $subdir . '/';
			$this->path = PUBLIC_FTP . $this->subdir;
		}
	}

	public function validate() {
		if (!isset($_POST['bestandsnaam'])) {
			$this->addError('Formulier niet compleet.');
		}
		$bestandsnaam = filter_input(INPUT_POST, 'bestandsnaam', FILTER_SANITIZE_STRING);
		if (!file_exists($this->path . $bestandsnaam)) {
			$this->addError('Bestand is niet aanwezig in de publieke FTP-map');
		}
		if ($this->getError() == '') {
			$this->createBestand($bestandsnaam, filesize($this->path . $bestandsnaam), null, $this->path . $bestandsnaam);
		}
		return $this->getError() == '';
	}

	public function verplaatsBestand($destination, $filename) {
		if (!is_writable($destination)) {
			throw new Exception('Doelmap is niet beschrijfbaar');
		}
		if (file_exists($this->path . $this->getBestand()->bestandsnaam)) {
			$gelukt = copy($this->path . $this->getBestand()->bestandsnaam, $destination . $filename);
			// Moeten we het bestand ook verwijderen uit de publieke ftp?
			if ($gelukt AND isset($_POST['verwijderVanFtp'])) {
				return unlink($this->path . $this->getBestand()->bestandsnaam);
			}
			return $gelukt;
		} else {
			throw new Exception('Bronbestand bestaat niet');
		}
	}

}
