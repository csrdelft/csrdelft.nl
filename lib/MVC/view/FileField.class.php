<?php

require_once 'MVC/model/entity/Afbeelding.class.php';

/**
 * FileField.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Verschillende manieren om een bestand te uploaden.
 */
class FileField implements FormElement, Validator {

	protected $opties;
	protected $methode;
	protected $filter;
	protected $behouden;
	protected $name;  // naam van het veld in POST
	public $not_null = false; // required

	public function __construct($name, Bestand $behouden = null, $ftpSubDir = '', array $filterMime = array()) {
		$this->name = $name;
		$this->opties = array(
			'BestandBehouden'	 => new BestandBehouden($name, $behouden),
			'UploadHttp'		 => new UploadHttp($name, $filterMime),
			'UploadFtp'			 => new UploadFtp($name, $ftpSubDir),
			'UploadUrl'			 => new UploadUrl($name)
		);
		$this->filter = $filterMime;
		$this->behouden = $behouden;
		foreach ($this->opties as $methode => $uploader) {
			if (!$uploader->isAvailable()) {
				unset($this->opties[$methode]);
			} else {
				$this->opties[$methode]->not_null = $this->not_null;
			}
		}
		if (isset($_POST[$name . 'BestandUploader'])) {
			$this->methode = filter_input(INPUT_POST, $name . 'BestandUploader', FILTER_SANITIZE_STRING);
		} elseif ($behouden !== null) {
			$this->methode = 'BestandBehouden';
		} else {
			$this->methode = 'UploadHttp';
		}
		if (!isset($this->opties[$this->methode])) {
			throw new Exception('Niet ondersteunde uploadmethode');
		}
		$this->opties[$this->methode]->selected = true;
	}

	public function getTitel() {
		return 'Bestand uploaden';
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->methode;
	}

	public function getModel() {
		return $this->opties[$this->methode]->getModel();
	}

	public function getError() {
		return $this->opties[$this->methode]->getError();
	}

	public function validate() {
		if (!$this->opties[$this->methode]->validate()) {
			return false;
		}
		if (!empty($this->filter) AND ! in_array($this->getModel()->mimetype, $this->filter)) {
			if (empty($this->getModel()->mimetype)) {
				if ($this->not_null) {
					$this->opties[$this->methode]->error = 'Afbeelding is verplicht';
					return false;
				}
				return true;
			}
			$this->opties[$this->methode]->error = 'Bestandstype niet toegestaan: ' . $this->getModel()->mimetype;
			return false;
		}
		return true;
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		if (!valid_filename($filename)) {
			throw new Exception('Ongeldige bestandsnaam');
		}
		if ($this->methode !== 'BestandBehouden') {
			if ($this->behouden !== null) {
				unlink($this->behouden->directory . $this->behouden->filename);
			}
			$filename = filter_var($filename, FILTER_SANITIZE_STRING);
			if (!file_exists($destination)) {
				mkdir($destination);
				chmod($destination, 0755);
			}
			if (!is_writable($destination)) {
				throw new Exception('Doelmap is niet beschrijfbaar: ' . $destination);
			}
			if (file_exists($destination . $filename)) {
				if ($overwrite) {
					unlink($destination . $filename);
				} else {
					throw new Exception('Bestandsnaam al in gebruik: ' . $filename);
				}
			}
		}
		$success = $this->opties[$this->methode]->opslaan($destination, $filename, $overwrite);
		if ($success) {
			chmod($destination . $filename, 0644);
		}
		return $success;
	}

	public function view() {
		foreach ($this->opties as $methode => $uploader) {
			$uploader->view();
		}
	}

	public function getJavascript() {
		return <<<JS
jQuery('input.UploadOptie').change(function() {
	var optie = jQuery('input.UploadOptie:checked');
	optie.css('visibility', 'hidden');
	jQuery('input.UploadOptie').not(optie).css('visibility', 'visible');
	var keuze = jQuery('div.UploadKeuze', optie.parent());
	jQuery('div.UploadKeuze').not(keuze).slideUp(250);
	keuze.slideDown(250);
});
jQuery('.knop.reset').click(function() {
	jQuery('#{$this->name}BestandBehoudenOptie').click();
});
JS;
	}

}

class RequiredFileField extends FileField {

	public $not_null = true;

}

class ImageField extends FileField {

	protected $minWidth;
	protected $minHeight;
	protected $maxWidth;
	protected $maxHeight;

	public function __construct($name, Afbeelding $behouden = null, $ftpSubDir = '', array $filterMime = null, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null) {
		parent::__construct($name, $behouden, $ftpSubDir, ($filterMime === null ? Afbeelding::$mimeTypes : $filterMime));
		$this->minWidth = $minWidth;
		$this->minHeight = $minHeight;
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getModel() instanceof Afbeelding) {
			$width = $this->getModel()->breedte;
			$height = $this->getModel()->hoogte;
			if ($this->minWidth !== null AND $width < $this->minWidth) {
				$this->opties[$this->methode]->error = 'Afbeelding is niet breed genoeg.';
			} elseif ($this->minHeight !== null AND $height < $this->minHeight) {
				$this->opties[$this->methode]->error = 'Afbeelding is niet hoog genoeg.';
			} elseif ($this->maxWidth !== null AND $width > $this->maxWidth) {
				$this->opties[$this->methode]->error = 'Afbeelding is te breed.';
			} elseif ($this->maxHeight !== null AND $height > $this->maxHeight) {
				$this->opties[$this->methode]->error = 'Afbeelding is te hoog.';
			}
		} else {
			if ($this->not_null) {
				$this->opties[$this->methode]->error = 'Afbeelding is verplicht';
			}
		}
		return $this->opties[$this->methode]->error === '';
	}

}

class RequiredImageField extends ImageField {

	public $not_null = true;

}

abstract class BestandUploader extends InputField {

	protected $uploaderName;
	public $selected = false;

	public function __construct($name) {
		parent::__construct($name . get_class($this), null, 'Bestand uploaden');
		$this->uploaderName = $name . 'BestandUploader';
	}

	public function isPosted() {
		return filter_input(INPUT_POST, $this->uploaderName, FILTER_SANITIZE_STRING) === $this->getType();
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
	public abstract function opslaan($destination, $filename);

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
	}

}

class BestandBehouden extends BestandUploader {

	public function __construct($name, Bestand $bestand = null) {
		parent::__construct($name);
		$this->model = $bestand;
	}

	public function isAvailable() {
		return (boolean) $this->model;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!$this->isAvailable()) {
			$this->error = 'Er is geen bestand om te behouden.';
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename) {
		if (!file_exists($destination . $filename)) {
			setMelding('Bestand bestaat niet (meer): ' . mb_htmlentities($destination . $filename), -1);
		}
		return true;
	}

	public function getLabel() {
		$label = '<input type="radio" class="UploadOptie" name="' . $this->uploaderName . '" id="' . $this->name . 'Optie" value="BestandBehouden"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		$label .= ' /><label for="' . $this->name . 'Optie">Huidig bestand behouden</label>';
		return $label;
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="' . $this->name . 'Keuze"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><div id="' . $this->name . '" style="height: 2em;">';
		echo $this->model->filename . ' (' . format_filesize($this->model->filesize) . ')';
		echo '</div></div></div>';
	}

}

class UploadHttp extends BestandUploader {

	private $filterMime;

	public function __construct($name, $filterMime) {
		parent::__construct($name);
		$this->filterMime = $filterMime;
		if ($this->isPosted()) {
			$this->value = $_FILES[$this->name];
			if (in_array($this->value['type'], Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($this->value['tmp_name']);
			} else {
				$this->model = new Bestand();
			}
			$this->model->filename = $this->value['name'];
			$this->model->filesize = $this->value['size'];
			$this->model->mimetype = $this->value['type'];
		}
	}

	public function isAvailable() {
		return true;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value['error'] == UPLOAD_ERR_NO_FILE) {
			if ($this->not_null) {
				$this->error = 'Selecteer een bestand';
			}
		} elseif ($this->value['error'] == UPLOAD_ERR_INI_SIZE) {
			$this->error = 'Bestand is te groot: Maximaal ' . ini_get('upload_max_filesize') . 'B';
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: code ' . $this->value['error'];
			DebugLogModel::instance()->log(get_called_class(), 'validate', array(), $this->error);
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename) {
		if (is_uploaded_file($this->value['tmp_name'])) {
			return move_uploaded_file($this->value['tmp_name'], $destination . $filename);
		}
		return false;
	}

	public function getLabel() {
		$label = '<input type="radio" class="UploadOptie" name="' . $this->uploaderName . '" id="' . $this->name . 'Optie" value="UploadHttp"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="' . $this->name . 'Optie"> Uploaden in browser</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="' . $this->name . 'Keuze"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><input type="file" class="' . implode(' ', $this->css_classes) . '" id="' . $this->name . '" name="' . $this->name . '" accept="' . implode('|', $this->filterMime) . '" /></div></div>';
	}

}

/**
 * UploadFtp ophalen van bestand nadat gebruiker
 * handmatig heeft geupload met FTP
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
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

	/**
	 * Trailing slash required for subdir!
	 * 
	 * @param string $name
	 * @param string $subdir
	 */
	public function __construct($name, $subdir) {
		parent::__construct($name);
		$this->subdir = $subdir;
		$this->path = PUBLIC_FTP . $this->subdir;
		if ($subdir != '' AND ( startsWith($subdir, '/') OR ! endsWith($subdir, '/') )) {
			throw new Exception('Invalid FTP subdir');
		}
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_STRING);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $this->path . $this->value);
			finfo_close($finfo);
			if (in_array($mime, Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($this->path . $this->value);
			} else {
				$this->model = new Bestand();
			}
			$this->model->filename = $this->value;
			$this->model->filesize = filesize($this->path . $this->value);
			$this->model->mimetype = $mime;
		}
	}

	public function isAvailable() {
		return file_exists($this->path) AND is_dir($this->path);
	}

	public function getFileList() {
		if (!$this->file_list) {
			$this->file_list = array();
			$handler = opendir($this->path);
			while ($file = readdir($handler)) {
				// We willen geen directories en geen verborgen bestanden.
				if (substr($file, 0, 1) !== '.' AND ! is_dir($this->path . $file)) {
					$this->file_list[] = $file;
				}
			}
			closedir($handler);
		}
		return $this->file_list;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!file_exists($this->path . $this->value)) {
			$this->error = 'Bestand is niet (meer) aanwezig';
		}
		if (is_dir($this->path . $this->value)) {
			$this->error = 'Selecteer een bestand';
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename) {
		if (!file_exists($this->path . $this->model->filename)) {
			throw new Exception('Bronbestand bestaat niet');
		}
		$gelukt = copy($this->path . $this->model->filename, $destination . $filename);
		// Moeten we het bestand ook verwijderen uit de publieke ftp?
		if ($gelukt AND isset($_POST[$this->name . 'VerwijderVanFtp'])) {
			return unlink($this->path . $this->model->filename);
		}
		return $gelukt;
	}

	public function getLabel() {
		$label = '<input type="radio" class="UploadOptie" name="' . $this->uploaderName . '" id="' . $this->name . 'Optie" value="UploadFtp"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="' . $this->name . 'Optie"> Uit publieke FTP-map</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="' . $this->name . 'Keuze"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '>';
		if (count($this->getFileList()) > 0) {
			echo '<select id="' . $this->name . '" name="' . $this->name . '" class="' . implode(' ', $this->css_classes) . '">';
			foreach ($this->getFileList() as $filename) {
				echo '<option value="' . htmlspecialchars($filename) . '"';
				if ($this->model AND $this->model->filename === $filename) {
					echo ' selected="selected"';
				}
				echo '>' . htmlspecialchars($filename) . '</option>';
			}
			echo '</select><br /><input type="checkbox" name="' . $this->name . 'VerwijderVanFtp" id="' . $this->name . 'VerwijderVanFtp" style="vertical-align: middle;"';
			if (!$this->isPosted() OR isset($_POST[$this->name . 'VerwijderVanFtp'])) {
				echo ' checked="checked"';
			}
			echo ' /><label for="verwijderVanFtp" style="float: none;"> Bestand verwijderen uit FTP-map</label>';
		} else {
			echo 'Geen bestanden gevonden in:<br />ftp://csrdelft.nl/incoming/csrdelft/' . $this->subdir;
		}
		echo '</div></div>';
	}

}

/**
 * UploadUrl een bestand downloaden van een url, met file_get_contents of de
 * cURL-extensie. Als beide niet beschikbaar zijn wordt het formulier-
 * element niet weergegeven.
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class UploadUrl extends BestandUploader {

	protected $url;

	public function __construct($name, $url = 'http://') {
		parent::__construct($name);
		$this->url = $url;
		if ($this->isPosted()) {
			$this->url = $_POST[$this->name];
			$this->value = $this->file_get_contents($this->url);
			if (!$this->value) {
				$this->error = 'Niets gevonden op url';
				return;
			}
			$url_name = substr(trim($this->url), strrpos($this->url, '/') + 1);
			$clean_name = preg_replace('/[^a-zA-Z0-9\s\.\-\_]/', '', $url_name);
			// Bestand tijdelijk omslaan om mime-type te bepalen
			$tmp_bestand = TMP_PATH . LoginModel::getUid() . '_' . time();
			if (!is_writable(TMP_PATH)) {
				$this->error = 'TMP_PATH is niet beschrijfbaar';
				return;
			}
			$filesize = file_put_contents($tmp_bestand, $this->value);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $tmp_bestand);
			finfo_close($finfo);
			if (in_array($mime, Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($tmp_bestand);
			} else {
				$this->model = new Bestand();
			}
			$this->model->filename = $clean_name;
			$this->model->filesize = $filesize;
			$this->model->mimetype = $mime;
			unlink($tmp_bestand);
		}
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
			return @file_get_contents($url);
		} else {
			return $this->curl_file_get_contents($url);
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!$this->isAvailable()) {
			$this->error = 'PHP.ini configuratie: cURL of allow_url_fopen moet aan staan.';
		} elseif (!url_like(urldecode($this->url))) {
			$this->error = 'Ongeldige url.';
		} elseif (empty($this->value)) {
			$this->error = 'Bestand is leeg, check de url.';
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename) {
		return file_put_contents($destination . $filename, $this->value);
	}

	public function getLabel() {
		$label = '<input type="radio" class="UploadOptie" name="' . $this->uploaderName . '" id="' . $this->name . 'Optie" value="UploadUrl"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="' . $this->name . 'Optie"> Downloaden van URL</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="' . $this->name . 'Keuze"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><input type="text" class="' . implode(' ', $this->css_classes) . '" id="' . $this->name . '" name="' . $this->name . '" value="' . $this->url . '" /></div></div>';
	}

}
