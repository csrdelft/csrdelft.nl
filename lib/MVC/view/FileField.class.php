<?php

require_once 'MVC/model/entity/Bestand.class.php';

/**
 * UploadElement.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Verschillende manieren om een bestand te uploaden.
 */
class FileField extends FormElement implements Validator {

	protected $methode;
	protected $filter;

	public function __construct($ftpSubDir = '', Bestand $behouden = null, array $filterType = array()) {
		parent::__construct(array(
			'BestandBehouden' => new BestandBehouden($behouden),
			'UploadHttp' => new UploadHttp(),
			'UploadFtp' => new UploadFtp($ftpSubDir),
			'UploadUrl' => new UploadUrl()
		));
		$this->filter = $filterType;
		foreach ($this->model as $methode => $uploader) {
			if (!$uploader->isBeschikbaar()) {
				unset($this->model[$methode]);
			}
		}
		if (isset($_POST['BestandUploader'])) {
			$this->methode = filter_input(INPUT_POST, 'BestandUploader');
		} elseif ($behouden !== null) {
			$this->methode = 'BestandBehouden';
		} else {
			$this->methode = 'UploadHttp';
		}
		if (!isset($this->model[$this->methode])) {
			throw new Exception('Niet ondersteunde uploadmethode');
		}
		$this->model[$this->methode]->selected = true;
	}

	public function getType() {
		return $this->methode;
	}

	public function getModel() {
		return $this->model[$this->methode]->getModel();
	}

	public function getError() {
		return $this->model[$this->methode]->getError();
	}

	public function validate() {
		if (!$this->model[$this->methode]->validate()) {
			return false;
		}
		if (sizeof($this->filter) > 0 AND ! in_array($this->getModel()->mimetype, $this->filter)) {
			$this->model[$this->methode]->error = 'Bestandstype niet toegestaan: ' . $this->getModel()->mimetype;
			return false;
		}
		return true;
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		if ($this->methode !== 'BestandBehouden') {
			$filename = filter_var($filename, FILTER_SANITIZE_STRING);
			if (!is_writable($destination)) {
				throw new Exception('Doelmap is niet beschrijfbaar: ' . $destination);
			}
			if ($overwrite) {
				unlink($destination . $filename);
			}
			if (file_exists($destination . $filename)) {
				throw new Exception('Bestandsnaam al in gebruik: ' . $filename);
			}
		}
		return $this->model[$this->methode]->opslaan($destination, $filename, $overwrite);
	}

	public function view() {
		foreach ($this->model as $methode => $uploader) {
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
	jQuery('#BestandBehoudenInput').click();
});
JS;
	}

}

abstract class BestandUploader extends InputField {

	public $selected = false;

	public function __construct(Bestand $bestand = null) {
		parent::__construct(get_class($this), null, 'Bestand uploaden', $bestand);
	}

	public function isPosted() {
		return isset($_POST['BestandUploader']) AND filter_input(INPUT_POST, 'BestandUploader', FILTER_SANITIZE_STRING) === get_class($this);
	}

	/**
	 * Is deze uploadmethode beschikbaar?
	 * 
	 * @return boolean
	 */
	public abstract function isBeschikbaar();

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

	public function isBeschikbaar() {
		return (boolean) $this->model;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!$this->isBeschikbaar()) {
			$this->error = 'Er is geen bestand om te behouden.';
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename) {
		if (!file_exists($destination . $filename)) {
			throw new Exception('Bestand bestaat niet');
		}
		return true;
	}

	public function getLabel() {
		$label = '<input type="radio" class="UploadOptie" name="BestandUploader" id="BestandBehoudenInput" value="BestandBehouden"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="BestandBehoudenInput"> Huidig bestand behouden</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="BestandBehouden"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><div style="height: 2em;">' . $this->model->bestandsnaam . ' (' . format_filesize($this->model->size) . ')</div></div></div>';
	}

}

class UploadHttp extends BestandUploader {

	public function __construct() {
		parent::__construct();
		if ($this->isPosted()) {
			$this->value = $_FILES['bestand'];
			$this->model = new Bestand();
			$this->model->bestandsnaam = $this->value['name'];
			$this->model->size = $this->value['size'];
			$this->model->mimetype = $this->value['type'];
		}
	}

	public function isBeschikbaar() {
		return true;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value['error'] == UPLOAD_ERR_INI_SIZE) {
			$this->error = 'Bestand is te groot: Maximaal ' . ini_get('upload_max_filesize') . 'B';
		} elseif ($this->value['error'] == UPLOAD_ERR_NO_FILE) {
			$this->error = 'Selecteer een bestand';
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: error-code: ' . $this->value['error'];
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
		$label = '<input type="radio" class="UploadOptie" name="BestandUploader" id="UploadHttpInput" value="UploadHttp"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="UploadHttpInput"> Uploaden in browser</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="UploadHttp"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><input type="file" id="httpInput" name="bestand" /></div></div>';
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

	public function __construct($subdir) {
		parent::__construct();
		$this->subdir = $subdir . '/';
		$this->path = PUBLIC_FTP . $this->subdir;
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, 'bestandsnaam', FILTER_SANITIZE_STRING);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $this->path . $this->value);
			finfo_close($finfo);
			$this->model = new Bestand();
			$this->model->bestandsnaam = $this->value;
			$this->model->size = filesize($this->path . $this->value);
			$this->model->mimetype = $mime;
		}
	}

	public function isBeschikbaar() {
		return file_exists($this->path);
	}

	public function getFileList() {
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
		if (!file_exists($this->path . $this->model->bestandsnaam)) {
			throw new Exception('Bronbestand bestaat niet');
		}
		$gelukt = copy($this->path . $this->model->bestandsnaam, $destination . $filename);
		// Moeten we het bestand ook verwijderen uit de publieke ftp?
		if ($gelukt AND isset($_POST['verwijderVanFtp'])) {
			return unlink($this->path . $this->model->bestandsnaam);
		}
		return $gelukt;
	}

	public function getLabel() {
		$label = '<input type="radio" class="UploadOptie" name="BestandUploader" id="UploadFtpInput" value="UploadFtp"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="UploadFtpInput"> Uit publieke FTP-map</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="UploadFtp"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '>';
		if (count($this->getFileList()) > 0) {
			echo '<select id="ftpSelect" name="bestandsnaam">';
			foreach ($this->getFileList() as $filename) {
				echo '<option value="' . htmlspecialchars($filename) . '"';
				if ($this->model AND $this->model->bestandsnaam === $filename) {
					echo ' selected="selected"';
				}
				echo '>' . htmlspecialchars($filename) . '</option>';
			}
			echo '</select><br /><input type="checkbox" name="verwijderVanFtp" id="verwijderVanFtp" style="vertical-align: middle;"';
			if (!$this->isPosted() OR isset($_POST['verwijderVanFtp'])) {
				echo ' checked="checked"';
			}
			echo ' /><label for="verwijderVanFtp" style="float: none;"> Bestand verwijderen uit FTP-map</label>';
		} else {
			echo 'Geen bestanden gevonden in:<br />ftp://csrdelft.nl/incoming/csrdelft' . $this->subdir;
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

	protected $url = 'http://';

	public function __construct() {
		parent::__construct();
		if ($this->isPosted()) {
			$this->url = $_POST['url'];
			$this->value = $this->file_get_contents($this->url);
			if (!$this->value) {
				$this->error = 'Niets gevonden op url';
				return;
			}
			$naam = substr(trim($this->url), strrpos($this->url, '/') + 1);
			$naam = preg_replace("/[^a-zA-Z0-9\s\.\-\_]/", '', $naam);
			//Bestand tijdelijk omslaan om mime-type te bepalen.
			$tmp_bestand = TMP_PATH . '/BestandUploader' . LoginLid::instance()->getUid() . microtime() . '.tmp';
			if (!is_writable(TMP_PATH)) {
				$this->error = 'TMP_PATH is niet beschrijfbaar';
				return;
			}
			$size = file_put_contents($tmp_bestand, $this->value);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $tmp_bestand);
			finfo_close($finfo);
			$this->model = new Bestand();
			$this->model->bestandsnaam = $naam;
			$this->model->size = $size;
			$this->model->mimetype = $mime;
			unlink($tmp_bestand);
		}
	}

	public function isBeschikbaar() {
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
		if (!$this->isBeschikbaar()) {
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
		$label = '<input type="radio" class="UploadOptie" name="BestandUploader" id="UploadUrlInput" value="UploadUrl"';
		if ($this->selected) {
			$label .= ' checked="checked"';
			$label .= ' style="visibility: hidden;"';
		}
		return $label . ' /><label for="UploadUrlInput"> Downloaden van URL</label>';
	}

	public function view() {
		parent::view();
		echo '<div class="UploadKeuze" id="UploadUrl"';
		if (!$this->selected) {
			echo ' style="display: none;"';
		}
		echo '><input type="text" id="urlInput" name="url" value="' . $this->url . '" /></div></div>';
	}

}
