<?php

require_once 'mimemagic/MimeMagic.php'; //mediawiki's mime magic class

/**
 * BestandUploader.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Verschillende manieren om bestanden te uploaden.
 * BestandUploader defenieert wat standaardfunctionaliteit.
 * De andere classes zorgen voor de speciale functies.
 * @FIXME In BestandUploader::getAll() moet een eventueel nieuw object aan de array toegevoegd worden.
 */
abstract class BestandUploader implements Validator {

	protected $active = false;
	protected $error;
	protected $filename;
	protected $mimetype = 'application/octet-stream';
	protected $size;

	public function __construct() {
		
	}

	/**
	 * Is deze uploadmethode beschikbaar?
	 * @return boolean
	 */
	public abstract function isAvailable();

	/**
	 * Bestand uiteindelijk opslaan op de juiste plek.
	 */
	abstract public function movefile(Document $document);

	public function getError() {
		return $this->error;
	}

	protected function addError($error) {
		$this->error .= $error . "\n";
	}

	public function getNaam() {
		return get_class($this);
	}

	public function getFilename() {
		return $this->filename;
	}

	public function getMimetype() {
		return $this->mimetype;
	}

	public function getSize() {
		return $this->size;
	}

	public function isActive() {
		return $this->active;
	}

	/**
	 * Geef een array terug met de aanwezige uploadmethodes.
	 * Bij een nieuw document willen we geen bestand behouden, want er
	 * is nog helemaal geen bestand, dus die kunnen we uitsluiten.
	 */
	public static function getAll($document, $active, $bestand_behouden = true) {
		$methodes = array('BestandBehouden', 'UploadBrowser', 'UploadURL', 'UploadFTP');
		$return = array();
		foreach ($methodes as $methodenaam) {
			if (!$bestand_behouden AND $methodenaam == 'BestandBehouden') {
				continue;
			}
			$methode = new $methodenaam($document);
			if ($methode->isAvailable()) {
				$return[$methodenaam] = $methode;
				if ($active == $methodenaam) {
					$return[$methodenaam]->active = true;
				}
			}
		}
		return $return;
	}

}

class BestandBehouden extends BestandUploader {

	public $document = null;

	public function __construct(Document $document) {
		parent::__construct();
		$this->document = $document;
		$this->filename = $document->getBestandsnaam();
		$this->mimetype = $document->getMimetype();
		$this->size = $document->getSize();
	}

	public function isAvailable() {
		return true;
	}

	public function validate() {
		return true;
	}

	public function moveFile(Document $document) {
		//do nothing here.
		return true;
	}

}

class UploadBrowser extends BestandUploader {

	/**
	 * Relevante inhoud van $_FILES;
	 */
	private $file;

	public function isAvailable() {
		return true;
	}

	public function validate() {
		if (!isset($_FILES['file_upload'])) {
			$this->addError('Formulier niet compleet');
		}
		$this->file = $_FILES['file_upload'];
		if ($this->file['error'] != 0) {
			switch ($this->file['error']) {
				case 1:
					$this->addError('Bestand is te groot: Maximaal ' . ini_get('upload_max_filesize') . 'B ');
					break;
				case 4:
					$this->addError('Selecteer een bestand');
					break;
				default:
					$this->addError('Upload-error: error-code: ' . $this->file['error']);
			}
		}
		if ($this->getError() == '') {
			$this->filename = $this->file['name'];
			$this->mimetype = $this->file['type'];
			$this->size = $this->file['size'];
			return true;
		} else {
			return false;
		}
	}

	public function moveFile(Document $document) {
		return $document->moveUploaded($this->file['tmp_name']);
	}

}

/**
 * UploadURL
 *
 * Kan een bestand downloaden van een url, met file_get_contents of de
 * cURL-extensie. Als beide niet beschikbaar zijn wordt het formulier-
 * element niet weergegeven.
 */
class UploadURL extends BestandUploader {

	/**
	 * Het hele bestand
	 * @var string
	 */
	protected $file;
	protected $url = 'http://';

	public function isAvailable() {
		return $this->file_get_contents_available() OR function_exists('curl_init');
	}

	public function getUrl() {
		return $this->url;
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
			return @file_get_contents($url);
		} else {
			return $this->curl_file_get_contents($url);
		}
	}

	public function validate() {
		if (!$this->isAvailable()) {
			$this->addError('PHP.ini configuratie: cURL of allow_url_fopen moet aan staan...');
		}
		if (!isset($_POST['url'])) {
			$this->addError('Formulier niet compleet');
		}
		if (!url_like(urldecode($_POST['url']))) {
			$this->addError('Dit lijkt niet op een url...');
		}
		$this->url = $_POST['url'];
		if ($this->getError() == '') {
			$this->file = $this->file_get_contents($this->url);
			if (strlen($this->file) == 0) {
				$this->addError('Bestand is leeg, check de url.');
			} else {
				$naam = substr(trim($this->url), strrpos($this->url, '/') + 1);
				//Bestand tijdelijk omslaan om mime-type te bepalen.
				$tmpfile = TMP_PATH . 'docuketz0r' . microtime() . '.tmp';
				if (is_writable(TMP_PATH)) {
					file_put_contents($tmpfile, $this->file);
					$mimetype = MimeMagic::singleton()->guessMimeType($tmpfile);
					unlink($tmpfile);

					$this->filename = preg_replace("/[^a-zA-Z0-9\s\.\-\_]/", '', $naam);
					$this->mimetype = $mimetype;
					$this->size = strlen($this->file);
				} else {
					$this->addError('Ophalen vanaf url mislukt: TMP_PATH is niet beschrijfbaar.');
				}
			}
		}
		return $this->getError() == '';
	}

	public function moveFile(Document $document) {
		return $document->putFile($this->file);
	}

}

class UploadFTP extends BestandUploader {

	/**
	 * Naam van het gekozen bestand
	 * @var string 
	 */
	protected $file;
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

	public function __construct() {
		parent::__construct();
		$this->subdir = '/documenten';
		$this->path = PUBLIC_FTP . $this->subdir . '/';
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

	public function validate() {
		if (!isset($_POST['ftpfile'])) {
			$this->addError('Formulier niet compleet.');
		}
		if (!file_exists($this->path . $_POST['ftpfile'])) {
			$this->addError('Bestand is niet aanwezig in de publieke FTP-map');
		}
		if ($this->getError() == '') {
			$this->file = $_POST['ftpfile'];
			$this->filename = $_POST['ftpfile'];
			$this->size = filesize($this->path . $this->file);
			$this->mimetype = MimeMagic::singleton()->guessMimeType($this->path . $this->file);
		}
		return $this->getError() == '';
	}

	public function moveFile(Document $document) {
		if ($document->copyFile($this->path . $this->file)) {
			// Moeten we het bestand ook verwijderen uit de publieke ftp?
			if (isset($_POST['deleteFiles'])) {
				return unlink($this->path . $this->file);
			}
			return true;
		}
		return false;
	}

}
