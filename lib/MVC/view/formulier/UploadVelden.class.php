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
 * 	- FileField						uitbreiding van KeuzeRondjeField
 * 		* ImageField
 * 
 * 	- BestandUploader				uitbreiding van InputField
 * 		* BestandBehouden
 * 		* UploadHttp
 * 			- DropZoneUploader
 * 		* UploadExisting
 * 		* UploadUrl
 * 
 */
class FileField extends KeuzeRondjeField {

	protected $uploaders;
	/**
	 * Toegestane mime-types
	 * @var array
	 */
	private $filterMime;

	public function __construct($name, Bestand $bestand = null, Map $dir = null, array $filterMime = array(), $multiple = false) {
		$this->filterMime = $filterMime;
		$behouden = new BestandBehouden($name . 'b', $this->filterMime, $bestand);
		$http = new UploadHttp($name . 'h', $this->filterMime, $multiple);
		$existing = new UploadExisting($name . 'e', $this->filterMime, $dir);
		$url = new UploadUrl($name . 'u', $this->filterMime);
		$this->uploaders = array(
			get_class($behouden) => $behouden,
			get_class($http)	 => $http,
			get_class($existing) => $existing,
			get_class($url)		 => $url
		);
		$default = null;
		$opties = array();
		foreach ($this->uploaders as $methode => $uploader) {
			if ($uploader->isAvailable()) {
				if (!isset($default)) {
					$default = $methode;
				}
				$opties[$methode] = $uploader->getTitel();
				$this->uploaders[$methode]->required = $this->required; // FIXME: not all required at the same time...
			} else {
				unset($this->uploaders[$methode]);
			}
		}
		parent::__construct($name, $default, null, $opties);
	}

	public function isPosted() {
		if (!parent::isPosted()) {
			return false;
		}
		return filter_input(INPUT_POST, $this->groupName, FILTER_SANITIZE_STRING) === $this->value;
	}

	public function getFilter() {
		return $this->filterMime;
	}

	public function setFilter(array $filterMime) {
		$this->filterMime = $filterMime;
		foreach ($this->uploaders as $methode => $uploader) {
			$uploader->filterMime = $filterMime;
		}
	}

	public function getType() {
		return $this->value;
	}

	public function getModel() {
		return $this->uploaders[$this->value]->getModel();
	}

	public function getError() {
		return $this->uploaders[$this->value]->getError();
	}

	public function validate() {
		if (!$this->uploaders[$this->value]->validate()) {
			return false;
		}
		if (!empty($this->filterMime) AND ! in_array($this->getModel()->mimetype, $this->filterMime)) {
			if (empty($this->getModel()->mimetype)) {
				if ($this->required) {
					$this->uploaders[$this->value]->error = 'Afbeelding is verplicht';
					return false;
				}
				return true;
			}
			$this->uploaders[$this->value]->error = 'Bestandstype niet toegestaan: ' . $this->getModel()->mimetype;
			return false;
		}
		return true;
	}

	/**
	 * Bestand opslaan op de juiste plek.
	 *
	 * @param string $destination fully qualified path with trailing slash
	 * @param string $filename filename with extension
	 * @param boolean $overwrite allowed to overwrite existing file
	 * @throws Exception Ongeldige bestandsnaam, doelmap niet schrijfbaar of naam ingebruik
	 */
	public function opslaan($destination, $filename, $overwrite = false) {
		if (!$this->isAvailable()) {
			throw new Exception('Uploadmethode niet beschikbaar: ' . get_class($this));
		}
		if (!$this->validate()) {
			throw new Exception($this->getError());
		}
		if (!valid_filename($filename)) {
			throw new Exception('Ongeldige bestandsnaam: ' . htmlspecialchars($filename));
		}
		if (!file_exists($destination)) {
			mkdir($destination);
		}
		if (false === @chmod($path, 0755)) {
			throw new Exception('Geen eigenaar van: ' . htmlspecialchars($destination));
		}
		if (!is_writable($destination)) {
			throw new Exception('Doelmap is niet beschrijfbaar: ' . htmlspecialchars($destination));
		}
		if (file_exists($destination . $filename)) {
			if ($overwrite) {
				if (!unlink($destination . $filename)) {
					throw new Exception('Overschrijven mislukt: ' . htmlspecialchars($destination . $filename));
				}
			} else {
				throw new Exception('Bestandsnaam al in gebruik: ' . htmlspecialchars($destination . $filename));
			}
		}
		$this->uploaders[$this->value]->opslaan($destination, $filename, $overwrite);
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getHtml() {
		$html = '';
		foreach ($this->uploaders as $methode => $uploader) {
			$html .= $uploader->getDiv();
			$html .= $uploader->getLabel();
			$html .= $uploader->getErrorDiv();
			$html .= $uploader->getHtml();
			if ($uploader->preview) {
				$html .= $uploader->getPreviewDiv();
			}
			$html .= '</div>';
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

	public function __construct($name, Afbeelding $behouden = null, $ftpSubDir = null, array $filterMime = null, $multiple = false, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null) {
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
				$this->uploaders[$this->value]->error = 'Afbeelding is niet breed genoeg.';
			} elseif ($this->minHeight !== null AND $height < $this->minHeight) {
				$this->uploaders[$this->value]->error = 'Afbeelding is niet hoog genoeg.';
			} elseif ($this->maxWidth !== null AND $width > $this->maxWidth) {
				$this->uploaders[$this->value]->error = 'Afbeelding is te breed.';
			} elseif ($this->maxHeight !== null AND $height > $this->maxHeight) {
				$this->uploaders[$this->value]->error = 'Afbeelding is te hoog.';
			}
		} else {
			if ($this->required) {
				$this->uploaders[$this->value]->error = 'Afbeelding is verplicht';
			}
		}
		return $this->uploaders[$this->value]->error === '';
	}

}

class RequiredImageField extends ImageField {

	public $required = true;

}

/**
 * Bestaand bestand behouden.
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BestandBehouden extends InputField {

	public $filterMime;
	public $selected = false;

	public function __construct($name, array $filterMime, Bestand $bestand = null) {
		parent::__construct($name, null, 'Huidig bestand behouden', $bestand);
		$this->filterMime = $filterMime;
	}

	public function isAvailable() {
		return $this->model instanceof Bestand;
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
			throw new Exception('Bestand bestaat niet (meer): ' . htmlspecialchars($destination . $filename));
		}
		if (false === @chmod($destination . $filename, 0644)) {
			throw new Exception('Geen eigenaar van: ' . htmlspecialchars($destination . $filename));
		}
	}

	public function getHtml() {
		$html = '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->getId() . 'Keuze">';
		$html .= '<div id="' . $this->getId() . '" class="BestandBehoudenNaam">' . $this->model->filename . ' (' . format_filesize($this->model->filesize) . ')</div>';
		return $html . '</div>';
	}

}

/**
 * Uploaden van bestand in de browser over http(s).
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class UploadHttp extends InputField {

	public $filterMime;
	public $selected = false;
	public $type = 'file';
	protected $multiple;

	public function __construct($name, array $filterMime, $multiple = false) {
		parent::__construct($name, null, 'Uploaden in browser');
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
			$this->error = 'Bestand is te groot: Maximaal ' . format_filesize(getMaximumFileUploadSize());
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: code ' . $this->value['error'];
			DebugLogModel::instance()->log(get_class($this), 'validate', array(), $this->error);
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		if (is_uploaded_file($this->value['tmp_name'])) {
			$moved = @move_uploaded_file($this->value['tmp_name'], $destination . $filename);
			if (!$moved) {
				throw new Exception('Verplaatsen mislukt: ' . htmlspecialchars($this->value['tmp_name']));
			}
		} else {
			throw new Exception('Bestand bestaat niet (meer): ' . htmlspecialchars($destination . $filename));
		}
		if (false === @chmod($destination . $filename, 0644)) {
			throw new Exception('Geen eigenaar van: ' . htmlspecialchars($destination . $filename));
		}
	}

	public function getHtml() {
		$html = '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->getId() . 'Keuze">';
		$html .= '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly')) . ' accept="' . implode('|', $this->filterMime) . '"' . ($this->multiple ? ' multiple' : '') . ' />';
		return $html . '</div>';
	}

	public function getJavascript() {
		$max = getMaximumFileUploadSize();
		$format = format_filesize($max);
		return parent::getJavascript() . <<<JS
if (typeof FileReader !== 'undefined') {
	if (document.getElementById('{$this->getId()}').files[0].size > {$max}) {
		alert('Bestand is te groot: Maximaal {$format}');
	}
}
JS;
	}

}

/**
 * Ophalen van bestand dat al op de server staat.
 * Bijvoorbeeld na uploaden met sFTP.
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class UploadExisting extends SelectField {

	public $filterMime;
	public $selected = false;
	private $dir;
	private $verplaats;

	public function __construct($name, array $filterMime, Map $root = null) {
		parent::__construct($name, null, 'Uit publieke FTP-map', array());
		$this->filterMime = $filterMime;
		if ($root === null) {
			$this->dir = new Map(PUBLIC_FTP);
		} else {
			$this->dir = $root;
		}
		if (!endsWith($this->dir->path, '/')) {
			$this->dir->path .= '/';
		}
		if ($this->dir->exists()) {
			$scan = scandir($this->dir->path, SCANDIR_SORT_ASCENDING);
			if (empty($scan)) {
				return false;
			}
			foreach ($scan as $entry) {
				if (is_file($this->dir->path . $entry)) {
					$name = htmlspecialchars($entry);
					$this->options[$name] = $name;
				}
			}
		}
		$this->verplaats = new VinkField($name . 'verplaats', false, null, 'Bestand verplaatsen');
		if ($this->isPosted()) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $this->dir->path . $this->value);
			finfo_close($finfo);
			if (in_array($mime, Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($this->dir->path . $this->value);
			} else {
				$this->model = new Bestand();
			}
			$this->model->filename = $this->value;
			$this->model->filesize = filesize($this->dir->path . $this->value);
			$this->model->mimetype = $mime;
		}
	}

	public function isAvailable() {
		return $this->dir instanceof Map AND $this->dir->exists();
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!$this->model OR is_dir($this->dir->path . $this->value)) {
			$this->error = 'Selecteer een bestand';
		}
		if (!$this->model->exists()) {
			$this->error = 'Bestand is niet (meer) aanwezig';
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		if (file_exists($this->dir->path . $this->model->filename)) {
			$copied = copy($this->dir->path . $this->model->filename, $destination . $filename);
			if (!$copied) {
				throw new Exception('Bestand kopieren mislukt: ' . htmlspecialchars($this->dir->path . $this->model->filename));
			}
		} else {
			throw new Exception('Bestand bestaat niet (meer): ' . htmlspecialchars($this->dir->path . $this->model->filename));
		}
		if (false === @chmod($destination . $filename, 0644)) {
			throw new Exception('Geen eigenaar van: ' . htmlspecialchars($destination . $filename));
		}
		// Moeten we het bestand ook verwijderen uit de publieke ftp?
		if (isset($_POST[$this->name . 'VerwijderVanFtp'])) {
			unlink($this->dir->path . $this->model->filename);
		}
	}

	public function getHtml() {
		$html = '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->getId() . 'Keuze">';
		if (sizeof($this->options) > 0) {
			$html .= parent::getHtml();
			$html .= '<br />';
			$html .= $this->verplaats->getHtml();
		} else {
			$html .= 'Geen bestanden gevonden in:<br />' . str_replace(PUBLIC_FTP, 'ftp://csrdelft.nl/incoming/csrdelft/', $this->dir->path);
		}
		return $html . '</div>';
	}

}

/**
 * Een bestand downloaden van een url, met file_get_contents of de
 * cURL-extensie. Als beide niet beschikbaar zijn wordt het formulier-
 * element niet weergegeven.
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class UploadUrl extends TextField {

	public $filterMime;
	public $selected = false;
	private $downloader;
	private $contents;

	public function __construct($name, array $filterMime) {
		parent::__construct($name, 'http://', 'Downloaden van URL');
		$this->filterMime = $filterMime;
		$this->downloader = new UrlDownloader();
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_URL);
			if (!url_like($this->value)) {
				return;
			}
			$this->contents = $this->file_get_contents($this->value);
			if (empty($this->contents)) {
				return;
			}
			$url_name = substr(trim($this->value), strrpos($this->value, '/') + 1);
			$clean_name = preg_replace('/[^a-zA-Z0-9\s\.\-\_]/', '', $url_name);
			// Bestand tijdelijk omslaan om mime-type te bepalen
			$tmp_bestand = TMP_PATH . LoginModel::getUid() . '_' . time();
			if (!is_writable(TMP_PATH)) {
				throw new Exception('TMP_PATH is niet beschrijfbaar');
			}
			$filesize = file_put_contents($tmp_bestand, $this->contents);
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
		} elseif (!url_like($this->value)) {
			$this->error = 'Ongeldige url';
		} elseif (empty($this->contents)) {
			$error = error_get_last();
			$pos = strrpos($error['message'], ': ') + 2;
			$this->error = substr($error['message'], $pos);
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, $overwrite);
		$put = file_put_contents($destination . $filename, $this->contents);
		if ($put === false) {
			throw new Exception('Bestand schrijven mislukt: ' . htmlspecialchars($destination . $filename));
		}
		if (false === @chmod($destination . $filename, 0644)) {
			throw new Exception('Geen eigenaar van: ' . htmlspecialchars($destination . $filename));
		}
	}

	public function getHtml() {
		$html = '<div class="UploadKeuze';
		if (!$this->selected) {
			$html .= ' verborgen';
		}
		$html .= '" id="' . $this->getId() . 'Keuze">';
		$html .= '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly')) . '/>';
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
