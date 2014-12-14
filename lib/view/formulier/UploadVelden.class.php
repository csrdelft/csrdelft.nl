<?php

require_once 'view/formulier/UrlDownloader.class.php';
require_once 'model/entity/Afbeelding.class.php';

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
 * 		* UploadFileField
 * 			- DropZoneUploader
 * 		* ExistingFileField
 * 		* DownloadUrlField
 * 
 */
class FileField extends KeuzeRondjeField {

	private $behoudenField;
	private $uploadField;
	private $existingField;
	private $urlField;
	protected $uploaders;

	public function __construct($name, $description, Bestand $bestand = null, Map $dir = null, array $filterMime = array(), $multiple = false) {
		$this->behoudenField = new BestandBehouden($name . '_BB', $filterMime, $bestand);
		$this->uploadField = new UploadFileField($name . '_HF', $filterMime, $multiple);
		$this->existingField = new ExistingFileField($name . '_EF', $filterMime, $dir);
		$this->urlField = new DownloadUrlField($name . '_DU', $filterMime);
		$this->uploaders = array(
			$this->behoudenField->name	 => $this->behoudenField,
			$this->uploadField->name	 => $this->uploadField,
			$this->existingField->name	 => $this->existingField,
			$this->urlField->name		 => $this->urlField
		);
		$default = null;
		$opties = array();
		foreach ($this->uploaders as $methode => $uploader) {
			if ($uploader->isAvailable()) {
				if (!isset($default)) {
					$default = $methode;
				}
				$opties[$methode] = $uploader->getTitel();
				$this->uploaders[$methode]->required = $this->required;
			} else {
				unset($this->uploaders[$methode]);
			}
		}
		parent::__construct($name, $default, $description, $opties);
	}

	public function isPosted() {
		if (!parent::isPosted()) {
			return false;
		}
		$methode = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_STRING);
		return $this->uploaders[$methode]->isPosted();
	}

	public function getType() {
		return $this->value;
	}

	public function getUploader() {
		if (!isset($this->uploaders[$this->value])) {
			throw new Exception('Upload method not available: ' . htmlspecialchars($this->value));
		}
		return $this->uploaders[$this->value];
	}

	public function getFilter() {
		return $this->getUploader()->getFilter();
	}

	public function getModel() {
		return $this->getUploader()->getModel();
	}

	public function getError() {
		return $this->getUploader()->getError();
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		return $this->getUploader()->validate();
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		$this->getUploader()->opslaan($destination, $filename, $overwrite);
	}

	public function getOptionHtml($methode, $description) {
		$html = '<div class="UploadOptie';
		if ($methode === $this->value) {
			$html .= ' verborgen';
		}
		$html .= '">';
		$html .= parent::getOptionHtml($methode, $description);
		$html .= '</div><div class="UploadKeuze';
		if ($methode !== $this->value) {
			$html .= ' verborgen';
		}
		$html .= '">';
		$html .= $this->uploaders[$methode]->getHtml();
		$html .= $this->uploaders[$methode]->getPreviewDiv();
		return $html . '</div>';
	}

	public function getJavascript() {
		$js = parent::getJavascript() . <<<JS

$('input[name="{$this->name}"]').change(function (event) {
	var aan = $('input[name="{$this->name}"]:checked').parent();
	aan.addClass('verborgen');
	aan.next('.UploadKeuze').slideDown(250);
	var uit = $('input[name="{$this->name}"]').parent().not(aan);
	uit.removeClass('verborgen');
	uit.next('.UploadKeuze').slideUp(250);
});
if ($('#{$this->behoudenField->getId()}')) {
	$('.reset').click(function() {
		$('#{$this->behoudenField->getId()}').click();
	});
}
JS;
		foreach ($this->uploaders as $methode => $uploader) {
			$js .= $uploader->getJavascript();
		}
		return $js;
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
	private $filterMime;

	public function __construct($name, $description, Afbeelding $behouden = null, Map $dir = null, array $filterMime = null, $multiple = false, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null) {
		$this->filterMime = $filterMime === null ? Afbeelding::$mimeTypes : array_intersect(Afbeelding::$mimeTypes, $filterMime);
		parent::__construct($name, $description, $behouden, $dir, $this->filterMime, $multiple);
		$this->minWidth = $minWidth;
		$this->minHeight = $minHeight;
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getModel() instanceof Afbeelding AND in_array($this->getModel()->mimetype, $this->filterMime)) {
			$width = $this->getModel()->width;
			$height = $this->getModel()->height;
			$resize = false;
			if ($this->minWidth !== null AND $width < $this->minWidth) {
				$resize = 'Afbeelding is niet breed genoeg. Minimaal ' . $this->minWidth . ' pixels.';
			} elseif ($this->minHeight !== null AND $height < $this->minHeight) {
				$resize = 'Afbeelding is niet hoog genoeg. Minimaal ' . $this->minHeight . ' pixels.';
			} elseif ($this->maxWidth !== null AND $width > $this->maxWidth) {
				$resize = 'Afbeelding is te breed. Maximaal ' . $this->maxWidth . ' pixels.';
			} elseif ($this->maxHeight !== null AND $height > $this->maxHeight) {
				$resize = 'Afbeelding is te hoog. Maximaal ' . $this->maxHeight . ' pixels.';
			}
			if ($resize) {
				$percentWidth = floor($this->maxWidth / $width);
				$percentHeight = floor($this->maxHeight / $height);
				if ($percentWidth < $percentHeight) {
					$percent = $percentWidth;
				} else {
					$percent = $percentHeight;
				}
				$directory = $this->getModel()->directory;
				$filename = $this->getModel()->filename;
				$resized = $directory . $percent . $filename;
				$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($directory . $filename) . ' -resize ' . $percent . '% -format jpg -quality 85 ' . escapeshellarg($resized);
				setMelding($command, 0);
				if (defined('RESIZE_OUTPUT')) {
					debugprint($command);
				}
				$output = shell_exec($command);
				if (defined('RESIZE_OUTPUT')) {
					debugprint($output);
				}
				if (false === @chmod($resized, 0644)) {
					$this->getUploader()->error = $resize;
				} else {
					$this->getModel()->filename = $percent . $filename;
					if (false === unlink($directory . $filename)) {
						$this->getUploader()->error = 'Origineel verwijderen na resizen mislukt!';
					}
				}
			}
		} else {
			if ($this->required) {
				$this->getUploader()->error = 'Afbeelding is verplicht';
			}
		}
		return $this->getUploader()->error === '';
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

	public function __construct($name, array $filterMime, Bestand $bestand = null) {
		parent::__construct($name, null, 'Huidig bestand behouden', $bestand);
		$this->filterMime = $filterMime;
	}

	public function isPosted() {
		return $this->isAvailable();
	}

	public function isAvailable() {
		return $this->model instanceof Bestand;
	}

	public function validate() {
		parent::validate();
		if (!$this->isAvailable()) {
			$this->error = 'Er is geen bestand om te behouden.';
		}
		return $this->error === '';
	}

	public function opslaan($destination, $filename, $overwrite = false) {
		parent::opslaan($destination, $filename, false);
		if (!file_exists($destination . $filename)) {
			throw new Exception('Bestand bestaat niet (meer): ' . htmlspecialchars($destination . $filename));
		}
		if (false === @chmod($destination . $filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($destination . $filename));
		}
	}

	public function getHtml() {
		return '<div ' . $this->getInputAttribute(array('id', 'name', 'class')) . '>' . $this->model->filename . ' (' . format_filesize($this->model->filesize) . ')</div>';
	}

	public function getPreviewDiv() {
		if ($this->getModel() instanceof Afbeelding) {
			$img = $this->getModel();
			return '<div id="imagePreview_' . $this->getId() . '" class="previewDiv"><img src="' . str_replace(PICS_PATH, CSR_PICS . '/', $img->directory) . $img->filename . '" width="' . $img->width . '" height="' . $img->height . '" /></div>';
		}
		return '';
	}

}

/**
 * Uploaden van bestand in de browser over http(s).
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class UploadFileField extends InputField {

	public $filterMime;
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

	public function isPosted() {
		return isset($_FILES[$this->name]);
	}

	public function isAvailable() {
		return true;
	}

	public function getFilter() {
		return $this->filterMime;
	}

	public function validate() {
		parent::validate();
		if (!empty($this->filterMime) AND ! empty($this->getModel()->mimetype) AND ! in_array($this->getModel()->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->getModel()->mimetype;
		}
		if ($this->value['error'] == UPLOAD_ERR_NO_FILE) {
			if ($this->required) {
				$this->error = 'Selecteer een bestand';
			}
		} elseif ($this->value['error'] == UPLOAD_ERR_INI_SIZE) {
			$this->error = 'Bestand is te groot: Maximaal ' . format_filesize(getMaximumFileUploadSize());
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: code ' . $this->value['error'];
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
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($destination . $filename));
		}
	}

	public function getHtml() {
		// werkomheen onbekende mime-types voor client
		if ($this->filterMime == Afbeelding::$mimeTypes) {
			$accept = 'image/*';
		} else {
			$accept = implode('|', $this->filterMime);
		}
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'disabled', 'readonly')) . ' accept="' . $accept . '"' . ($this->multiple ? ' multiple' : '') . ' data-max-size="' . getMaximumFileUploadSize() . '" />';
	}

	public function getJavascript() {
		$max = getMaximumFileUploadSize();
		$format = format_filesize($max);
		return parent::getJavascript() . <<<JS

$('#{$this->getId()}').change(function() {
	for (i = 0; i < this.files.length; i++) { 
		if (this.files[i].size > {$max}) {
			alert(this.files[i].name + ' is te groot: Maximaal {$format}\\n\\nSplits het bestand op of gebruik een andere upload-methode.');
			if (this.files.length <= 1) {
				$(this).val('');
			}
		}
	}
});
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
class ExistingFileField extends SelectField {

	public $filterMime;
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
					if (!empty($this->filterMime)) {
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$mimetype = finfo_file($finfo, $this->dir->path . $entry);
						finfo_close($finfo);
						if (in_array($mimetype, $this->filterMime)) {
							$this->options[$name] = $name;
						}
					} else {
						$this->options[$name] = $name;
					}
				}
			}
		}
		$this->verplaats = new VinkField($this->name . '_BV', false, null, 'Bestand verplaatsen');
		if ($this->isPosted()) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $this->dir->path . $this->value);
			finfo_close($finfo);
			if (in_array($mimetype, Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($this->dir->path . $this->value, true);
			} else {
				$this->model = new Bestand();
				$this->model->filename = $this->value;
				$this->model->filesize = filesize($this->dir->path . $this->value);
				$this->model->mimetype = $mimetype;
				$this->model->directory = $this->dir->path;
			}
		}
	}

	public function isAvailable() {
		return $this->dir instanceof Map AND $this->dir->exists();
	}

	public function getFilter() {
		return $this->filterMime;
	}

	public function validate() {
		parent::validate();
		if (!empty($this->filterMime) AND ! in_array($this->getModel()->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->getModel()->mimetype;
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
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($destination . $filename));
		}
		if ($this->verplaats->getValue()) {
			$moved = unlink($this->dir->path . $this->model->filename);
			if (!$moved) {
				throw new Exception('Verplaatsen mislukt: ' . htmlspecialchars($this->dir->path . $this->model->filename));
			}
		}
	}

	public function getHtml() {
		if (sizeof($this->options) > 0) {
			return parent::getHtml() . '<br />' . $this->verplaats->getHtml();
		} else {
			return 'Geen bestanden gevonden in:<br />' . str_replace(PUBLIC_FTP, 'ftp://csrdelft.nl/incoming/csrdelft/', $this->dir->path);
		}
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
class DownloadUrlField extends UrlField {

	public $filterMime;
	private $downloader;
	private $contents;

	public function __construct($name, array $filterMime) {
		parent::__construct($name, 'http://', 'Downloaden van URL');
		$this->filterMime = $filterMime;
		$this->downloader = new UrlDownloader();
		if ($this->isPosted()) {
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

	public function getFilter() {
		return $this->filterMime;
	}

	public function validate() {
		parent::validate();
		if (!empty($this->filterMime) AND ! in_array($this->getModel()->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->getModel()->mimetype;
		}
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
		$filename .= '.html';
		parent::opslaan($destination, $filename, $overwrite);
		$put = file_put_contents($destination . $filename, $this->contents);
		if ($put === false) {
			throw new Exception('Bestand schrijven mislukt: ' . htmlspecialchars($destination . $filename));
		}
		if (false === @chmod($destination . $filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($destination . $filename));
		}
	}

	public function getHtml() {
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly')) . '/>';
	}

}
