<?php

require_once 'MVC/view/formulier/UrlDownloader.class.php';
require_once 'MVC/model/entity/Afbeelding.class.php';

/**
 * UploadVelden.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Verschillende manieren om een bestand of afbeelding(en) te uploaden.
 * 
 * 	- FileField						uitbreiding van FormElement
 * 		* ImageField
 * 
 * 	- BestandUploader				uitbreiding van InputField
 * 		* BestandBehouden
 * 		* UploadHttp
 * 			- DropZoneUploader
 * 		* UploadFtp
 * 		* UploadUrl
 * 
 */
class FileField implements FormElement, Validator {

	/** @var BestandUploader[] */
	protected $opties;
	protected $methode;
	protected $filterMime;
	protected $behouden;
	protected $name;  // naam van het veld in POST
	public $required = false;

	public function __construct($name, Bestand $behouden = null, $ftpSubDir = '', array $filterMime = array(), $multiple = false) {
		$this->name = $name;
		$this->opties = array(
			'BestandBehouden'	 => new BestandBehouden($name, $behouden),
			'UploadHttp'		 => new UploadHttp($name, $filterMime, $multiple),
			'UploadFtp'			 => new UploadFtp($name, $ftpSubDir),
			'UploadUrl'			 => new UploadUrl($name)
		);
		$this->filterMime = $filterMime;
		$this->behouden = $behouden;
		foreach ($this->opties as $methode => $uploader) {
			if (!$uploader->isAvailable()) {
				unset($this->opties[$methode]);
			} else {
				$this->opties[$methode]->required = $this->required;
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
		return $this->getType();
	}

	public function getName() {
		return $this->name;
	}

	public function getFilter() {
		return $this->filterMime;
	}

	public function getType() {
		return $this->methode;
	}

	public function isPosted() {
		return $this->opties[$this->methode]->isPosted();
	}

	public function getModel() {
		return $this->opties[$this->methode]->getModel();
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getError() {
		return $this->opties[$this->methode]->getError();
	}

	public function validate() {
		if (!$this->opties[$this->methode]->validate()) {
			return false;
		}
		if (!empty($this->filterMime) AND ! in_array($this->getModel()->mimetype, $this->filterMime)) {
			if (empty($this->getModel()->mimetype)) {
				if ($this->required) {
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
		$this->opties[$this->methode]->opslaan($destination, $filename, $overwrite);
	}

	public function getHtml() {
		$html = '';
		foreach ($this->opties as $methode => $uploader) {
			$html .= $uploader->getHtml();
		}
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getJavascript() {
		return <<<JS

/* {$this->name} */
jQuery('input.UploadOptie').change(function() {
	var optie = jQuery('input.UploadOptie:checked');
	optie.addClass('verborgen');
	jQuery('input.UploadOptie').not(optie).removeClass('verborgen');
	var keuze = optie.next('div.UploadKeuze');
	jQuery('div.UploadKeuze').not(keuze).slideUp(250);
	keuze.slideDown(250);
});
jQuery('.btn.reset').click(function() {
	jQuery('#{$this->name}BestandBehoudenOptie').click();
});
JS;
	}

}

class RequiredFileField extends FileField {

	public $required = true;

}

class ImageField extends FileField {

	protected $minWidth;
	protected $minHeight;
	protected $maxWidth;
	protected $maxHeight;

	public function __construct($name, Afbeelding $behouden = null, $ftpSubDir = '', array $filterMime = null, $multiple = false, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null) {
		parent::__construct($name, $behouden, $ftpSubDir, ($filterMime === null ? Afbeelding::$mimeTypes : $filterMime), $multiple);
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
			$width = $this->getModel()->width;
			$height = $this->getModel()->height;
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
			if ($this->required) {
				$this->opties[$this->methode]->error = 'Afbeelding is verplicht';
			}
		}
		return $this->opties[$this->methode]->error === '';
	}

}

class RequiredImageField extends ImageField {

	public $required = true;

}

abstract class BestandUploader extends InputField {

	protected $groupName;
	public $selected = false;

	public function __construct($name) {
		parent::__construct($name . get_class($this), null, 'Bestand uploaden');
		$this->groupName = $name . 'BestandUploader';
	}

	public function isPosted() {
		return filter_input(INPUT_POST, $this->getGroupName(), FILTER_SANITIZE_STRING) === $this->getType();
	}

	/**
	 * Naam van de set bij elkaar horende bestanduploaders
	 * 
	 * @return string
	 */
	public function getGroupName() {
		return $this->groupName;
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
	 * @param boolean $overwrite allowed to overwrite existing file
	 * @throws Exception Ongeldige bestandsnaam, doelmap niet schrijfbaar of naam ingebruik
	 */
	public function opslaan($destination, $filename, $overwrite = false) {
		if (!valid_filename($filename)) {
			throw new Exception('Ongeldige bestandsnaam');
		}
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

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		if (!file_exists($destination . $filename)) {
			throw new Exception('Bestand bestaat niet (meer): ' . $filename);
		}
		chmod($destination . $filename, 0644);
	}

	protected function getLabel() {
		return '<label for="' . $this->name . 'Optie">Huidig bestand behouden</label>';
	}

	public function getHtml() {
		$html = $this->getDiv();
		$html .= $this->getLabel();
		$html .= $this->getErrorDiv();

		$html .= '<input type="radio" name="' . $this->groupName . '" id="' . $this->name . 'Optie" value="BestandBehouden" class="UploadOptie';
		if ($this->selected) {
			$html .= ' verborgen" checked="checked';
		}
		$html .= '" />';
		$html .= '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->name . 'Keuze">';
		$html .= '<div id="' . $this->name . '" class="BestandBehoudenNaam">';
		$html .= $this->model->filename . ' (' . format_filesize($this->model->filesize) . ')';
		$html .= '</div></div>';

		if ($this->preview) {
			$html .= $this->getPreviewDiv();
		}
		return $html . '</div>';
	}

}

class UploadHttp extends BestandUploader {

	protected $filterMime;
	protected $multiple;

	public function __construct($name, array $filterMime, $multiple = false) {
		parent::__construct($name);
		$this->filterMime = $filterMime;
		$this->multiple = $multiple;
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

	public function getFilter() {
		return $this->filterMime;
	}

	public function isAvailable() {
		return true;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value['error'] == UPLOAD_ERR_NO_FILE) {
			if ($this->required) {
				$this->error = 'Selecteer een bestand';
			}
		} elseif ($this->value['error'] == UPLOAD_ERR_INI_SIZE) {
			$this->error = 'Bestand is te groot: Maximaal ' . ini_get('upload_max_filesize') . 'B';
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: code ' . $this->value['error'];
			DebugLogModel::instance()->log(get_class($this), 'validate', array(), $this->error);
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		if (is_uploaded_file($this->value['tmp_name'])) {
			move_uploaded_file($this->value['tmp_name'], $destination . $filename);
			chmod($destination . $filename, 0644);
		}
	}

	protected function getLabel() {
		return '<label for="' . $this->name . 'Optie"> Uploaden in browser</label>';
	}

	public function getHtml() {
		$html = $this->getDiv();
		$html .= $this->getLabel();
		$html .= $this->getErrorDiv();

		$html .= '<input type="radio" name="' . $this->groupName . '" id="' . $this->name . 'Optie" value="UploadHttp" class="UploadOptie';
		if ($this->selected) {
			$html .=' verborgen" checked="checked';
		}
		$html .= '" />';
		$html .= '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->name . 'Keuze">';
		$html .= '<input type="file" class="' . implode(' ', $this->css_classes) . '" id="' . $this->name . '" name="' . $this->name . '" accept="' . implode('|', $this->filterMime) . '"' . ($this->multiple ? ' multiple' : '') . ' /></div>';

		if ($this->preview) {
			$html .= $this->getPreviewDiv();
		}
		return $html . '</div>';
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
	 * @throws Exception
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
			$handle = opendir($this->path);
			if (!$handle) {
				return $this->file_list;
			}
			while ($file = readdir($handle)) {
				// We willen geen directories en geen verborgen bestanden.
				if (substr($file, 0, 1) !== '.' AND ! is_dir($this->path . $file)) {
					$this->file_list[] = $file;
				}
			}
			closedir($handle);
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

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		if (!file_exists($this->path . $this->model->filename)) {
			throw new Exception('Bronbestand bestaat niet');
		}
		copy($this->path . $this->model->filename, $destination . $filename);
		chmod($destination . $filename, 0644);
		// Moeten we het bestand ook verwijderen uit de publieke ftp?
		if (isset($_POST[$this->name . 'VerwijderVanFtp'])) {
			unlink($this->path . $this->model->filename);
		}
	}

	protected function getLabel() {
		return '<label for="' . $this->name . 'Optie"> Uit publieke FTP-map</label>';
	}

	public function getHtml() {
		$html = $this->getDiv();
		$html .= $this->getLabel();
		$html .= $this->getErrorDiv();

		$html .= '<input type="radio" name="' . $this->groupName . '" id="' . $this->name . 'Optie" value="UploadFtp" class="UploadOptie';
		if ($this->selected) {
			$html .= ' verborgen" checked="checked';
		}
		$html .= '" />';
		$html .= '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->name . 'Keuze">';
		if (count($this->getFileList()) > 0) {
			$html .= '<select id="' . $this->name . '" name="' . $this->name . '" class="' . implode(' ', $this->css_classes) . '">';
			foreach ($this->getFileList() as $filename) {
				$html .= '<option value="' . htmlspecialchars($filename) . '"';
				if ($this->model AND $this->model->filename === $filename) {
					$html .= ' selected="selected"';
				}
				$html .= '>' . htmlspecialchars($filename) . '</option>';
			}
			$html .= '</select><br /><input type="checkbox" name="' . $this->name . 'VerwijderVanFtp" id="' . $this->name . 'VerwijderVanFtp" class="VerwijderVanFtpCheckbox"';
			if (!$this->isPosted() OR isset($_POST[$this->name . 'VerwijderVanFtp'])) {
				$html .= ' checked="checked"';
			}
			$html .= ' /><label for="verwijderVanFtp" class="VinkFieldLabel"> Bestand verwijderen uit FTP-map</label>';
		} else {
			$html .= 'Geen bestanden gevonden in:<br />ftp://csrdelft.nl/incoming/csrdelft/' . $this->subdir;
		}
		$html .= '</div>';

		if ($this->preview) {
			$html .= $this->getPreviewDiv();
		}
		return $html . '</div>';
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
	protected $downloader;

	public function __construct($name, $url = 'http://') {
		parent::__construct($name);
		$this->url = $url;
		$this->downloader = new UrlDownloader();
		if ($this->isPosted()) {
			$this->url = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_URL);
			if (!startsWith($this->url, 'http://') AND ! startsWith($this->url, 'https://')) {
				return;
			}
			$this->value = $this->file_get_contents($this->url);
			if (!$this->value) {
				return;
			}
			$url_name = substr(trim($this->url), strrpos($this->url, '/') + 1);
			$clean_name = preg_replace('/[^a-zA-Z0-9\s\.\-\_]/', '', $url_name);
			// Bestand tijdelijk omslaan om mime-type te bepalen
			$tmp_bestand = TMP_PATH . LoginModel::getUid() . '_' . time();
			if (!is_writable(TMP_PATH)) {
				throw new Exception('TMP_PATH is niet beschrijfbaar');
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
		return $this->downloader->isAvailable();
	}

	protected function file_get_contents($url) {
		return $this->downloader->file_get_contents($url);
	}

	public function validate() {
		parent::validate();
		// override met specifiekere foutmeldingen
		if (!$this->isAvailable()) {
			$this->error = 'PHP.ini configuratie: fsocked, cURL of allow_url_fopen moet aan staan.';
		} elseif (!startsWith($this->url, 'http://') AND ! startsWith($this->url, 'https://')) {
			$this->error = 'Ongeldige url';
		} elseif (empty($this->value)) {
			$error = error_get_last();
			$pos = strrpos($error['message'], ': ') + 2;
			$this->error = substr($error['message'], $pos);
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		file_put_contents($destination . $filename, $this->value);
		chmod($destination . $filename, 0644);
	}

	protected function getLabel() {
		return '<label for="' . $this->name . 'Optie"> Downloaden van URL</label>';
	}

	public function getHtml() {
		$html = $this->getDiv();
		$html .= $this->getLabel();
		$html .= $this->getErrorDiv();

		$html .= '<input type="radio" name="' . $this->groupName . '" id="' . $this->name . 'Optie" value="UploadUrl" class="UploadOptie';
		if ($this->selected) {
			$html .= ' verborgen" checked="checked';
		}
		$html .= '" />';
		$html .= '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->name . 'Keuze">';
		$html .= '<input type="text" class="' . implode(' ', $this->css_classes) . '" id="' . $this->name . '" name="' . $this->name . '" value="' . $this->url . '" /></div>';

		if ($this->preview) {
			$html .= $this->getPreviewDiv();
		}
		return $html . '</div>';
	}

}

class DropzoneUploader extends UploadHttp {

	public function isPosted() {
		return isset($_FILES[$this->name]);
	}

	public function view() {
		parent::getErrorDiv();
	}

}
