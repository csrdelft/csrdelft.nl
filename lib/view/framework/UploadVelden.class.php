<?php

require_once 'view/framework/UrlDownloader.class.php';
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

	public function __construct($name, $description, Bestand $bestand = null, Map $dir = null, array $filterMime = array()) {
		$this->behoudenField = new BestandBehouden($name . '_BB', $filterMime, $bestand);
		$this->uploadField = new UploadFileField($name . '_HF', $filterMime);
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

	public function opslaan($directory, $filename, $overwrite = false) {
		$this->getUploader()->opslaan($directory, $filename, $overwrite);
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

	protected $vierkant;
	protected $minWidth;
	protected $minHeight;
	protected $maxWidth;
	protected $maxHeight;
	private $filterMime;

	public function __construct($name, $description, Afbeelding $behouden = null, Map $dir = null, array $filterMime = null, $vierkant = false, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null) {
		$this->filterMime = $filterMime === null ? Afbeelding::$mimeTypes : array_intersect(Afbeelding::$mimeTypes, $filterMime);
		parent::__construct($name, $description, $behouden, $dir, $this->filterMime);
		$this->vierkant = $vierkant;
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
			if ($this->vierkant AND $width !== $height) {
				$resize = 'Afbeelding is niet vierkant.';
			} else {
				if ($this->maxWidth !== null AND $width > $this->maxWidth) {
					$resize = 'Afbeelding is te breed. Maximaal ' . $this->maxWidth . ' pixels.';
					$smallerW = floor((float) $this->maxWidth * 100 / (float) $width);
				} elseif ($this->minWidth !== null AND $width < $this->minWidth) {
					$resize = 'Afbeelding is niet breed genoeg. Minimaal ' . $this->minWidth . ' pixels.';
					$biggerW = ceil((float) $this->minWidth * 100 / (float) $width);
				}
				if ($this->maxHeight !== null AND $height > $this->maxHeight) {
					$resize = 'Afbeelding is te hoog. Maximaal ' . $this->maxHeight . ' pixels.';
					$smallerH = floor((float) $this->maxHeight * 100 / (float) $height);
				} elseif ($this->minHeight !== null AND $height < $this->minHeight) {
					$resize = 'Afbeelding is niet hoog genoeg. Minimaal ' . $this->minHeight . ' pixels.';
					$biggerH = ceil((float) $this->minHeight * 100 / (float) $height);
				}
			}
			if ($resize) {
				if ($this->vierkant) {
					$percent = 'vierkant';
				} elseif (isset($biggerW, $smallerH) OR isset($biggerH, $smallerW)) {
					$this->getUploader()->error = 'Geen resize verhouding';
					return false;
				} elseif (isset($smallerW, $smallerH)) {
					$percent = min(array($smallerW, $smallerH));
				} elseif (isset($biggerW, $biggerH)) {
					$percent = max(array($biggerW, $biggerH));
				} elseif (isset($smallerW)) {
					$percent = $smallerW;
				} elseif (isset($biggerW)) {
					$percent = $biggerW;
				} elseif (isset($smallerH)) {
					$percent = $smallerH;
				} elseif (isset($biggerH)) {
					$percent = $biggerH;
				} else {
					$percent = 100;
				}
				$directory = $this->getModel()->directory;
				$filename = $this->getModel()->filename;
				$resized = $directory . $percent . $filename;
				if ($this->vierkant) {
					$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($directory . $filename) . ' -thumbnail 150x150^ -gravity center -extent 150x150 -format jpg -quality 80 ' . escapeshellarg($resized);
				} else {
					$command = IMAGEMAGICK_PATH . 'convert ' . escapeshellarg($directory . $filename) . ' -resize ' . $percent . '% -format jpg -quality 85 ' . escapeshellarg($resized);
				}
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
		return $this->model instanceof Bestand AND $this->model->exists();
	}

	public function validate() {
		parent::validate();
		if (!$this->isAvailable() OR empty($this->model->filesize)) {
			$this->error = 'Bestand bestaat niet (meer): ' . htmlspecialchars($this->model->directory . $this->model->filename);
		} elseif (!empty($this->filterMime) AND ! in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . htmlspecialchars($this->model->mimetype);
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, false);
		if (false === @chmod($this->model->directory . $this->model->filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($this->model->directory . $this->model->filename));
		}
	}

	public function getHtml() {
		return '<div ' . $this->getInputAttribute(array('id', 'name', 'class')) . '>' . $this->model->filename . ' (' . format_filesize($this->model->filesize) . ')</div>';
	}

	public function getPreviewDiv() {
		if ($this->model instanceof Afbeelding) {
			return '<div id="imagePreview_' . $this->getId() . '" class="previewDiv"><img src="' . str_replace(PICS_PATH, CSR_ROOT . '/plaetjes/', $this->model->directory) . $this->model->filename . '" width="' . $this->model->width . '" height="' . $this->model->height . '" /></div>';
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

	public function __construct($name, array $filterMime) {
		parent::__construct($name, null, 'Uploaden in browser');
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
			$this->model->directory = dirname($this->value['tmp_name']);
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
		if ($this->value['error'] == UPLOAD_ERR_NO_FILE) {
			if ($this->required) {
				$this->error = 'Selecteer een bestand';
			}
		} elseif ($this->value['error'] == UPLOAD_ERR_INI_SIZE) {
			$this->error = 'Bestand is te groot: Maximaal ' . format_filesize(getMaximumFileUploadSize());
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: code ' . $this->value['error'];
		} elseif (!is_uploaded_file($this->value['tmp_name'])OR empty($this->model->filesize)) {
			$this->error = 'Bestand bestaat niet (meer): ' . htmlspecialchars($this->value['tmp_name']);
		} elseif (!empty($this->filterMime) AND ! in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . htmlspecialchars($this->model->mimetype);
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, $overwrite);
		$moved = @move_uploaded_file($this->value['tmp_name'], $directory . $filename);
		if (!$moved) {
			throw new Exception('Verplaatsen mislukt: ' . htmlspecialchars($this->value['tmp_name']));
		}
		if (false === @chmod($directory . $filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($directory . $filename));
		}
		$this->model->directory = $directory;
		$this->model->filename = $filename;
	}

	public function getHtml() {
		// werkomheen onbekende mime-types voor client
		if ($this->filterMime == Afbeelding::$mimeTypes) {
			$accept = 'image/*';
		} else {
			$accept = implode('|', $this->filterMime);
		}
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'disabled', 'readonly')) . ' accept="' . $accept . '" data-max-size="' . getMaximumFileUploadSize() . '" />';
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
				$this->model = new Afbeelding($this->dir->path . $this->value);
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
		if (!$this->isAvailable() OR ! ($this->model instanceof Bestand) OR ! $this->model->exists()OR empty($this->model->filesize)) {
			$this->error = 'Bestand is niet (meer) aanwezig';
		} elseif (!empty($this->filterMime) AND ! in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->model->mimetype;
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, $overwrite);
		$copied = copy($this->model->directory . $this->model->filename, $directory . $filename);
		if (!$copied) {
			throw new Exception('Bestand kopieren mislukt: ' . htmlspecialchars($this->model->directory . $this->model->filename));
		}
		if (false === @chmod($directory . $filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($directory . $filename));
		}
		if ($this->verplaats->getValue()) {
			$moved = unlink($this->model->directory . $this->model->filename);
			if (!$moved) {
				throw new Exception('Verplaatsen mislukt: ' . htmlspecialchars($this->model->directory . $this->model->filename));
			}
		}
		$this->model->directory = $directory;
		$this->model->filename = $filename;
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
	private $tmp_file;

	public function __construct($name, array $filterMime) {
		parent::__construct($name, 'http://', 'Downloaden van URL');
		$this->filterMime = $filterMime;
		$this->downloader = new UrlDownloader();
		if ($this->isPosted()) {
			if (!url_like($this->value)) {
				return;
			}
			$data = $this->downloader->file_get_contents($this->value);
			if (empty($data)) {
				return;
			}
			$url_name = substr(trim($this->value), strrpos($this->value, '/') + 1);
			$clean_name = preg_replace('/[^a-zA-Z0-9\s\.\-\_]/', '', $url_name);
			$this->tmp_file = TMP_PATH . $clean_name;
			if (!is_writable(TMP_PATH)) {
				throw new Exception('TMP_PATH is niet beschrijfbaar');
			}
			$filesize = file_put_contents($this->tmp_file, $data);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $this->tmp_file);
			finfo_close($finfo);
			if (in_array($mimetype, Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($this->tmp_file);
			} else {
				$this->model = new Bestand();
				$this->model->filename = $clean_name;
				$this->model->filesize = $filesize;
				$this->model->mimetype = $mimetype;
				$this->model->directory = TMP_PATH;
			}
		}
	}

	public function isAvailable() {
		return $this->downloader->isAvailable();
	}

	public function getFilter() {
		return $this->filterMime;
	}

	public function validate() {
		parent::validate();
		if (!$this->isAvailable()) {
			$this->error = 'PHP.ini configuratie: fsocked, cURL of allow_url_fopen moet aan staan.';
		} elseif (!url_like($this->value)) {
			$this->error = 'Ongeldige url';
		} elseif (!$this->model instanceof Bestand OR ! $this->model->exists() OR empty($this->model->filesize)) {
			$error = error_get_last();
			$this->error = $error['message'];
		} elseif (!empty($this->filterMime) AND ! in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->model->mimetype;
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, $overwrite);
		$copied = copy($this->model->directory . $this->model->filename, $directory . $filename);
		if (!$copied) {
			throw new Exception('Bestand kopieren mislukt: ' . htmlspecialchars($this->model->directory . $this->model->filename));
		}
		$moved = unlink($this->model->directory . $this->model->filename);
		if (!$moved) {
			throw new Exception('Verplaatsen mislukt: ' . htmlspecialchars($this->model->directory . $this->model->filename));
		}
		if (false === @chmod($directory . $filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($directory . $filename));
		}
		$this->model->directory = $directory;
		$this->model->filename = $filename;
	}

	public function getHtml() {
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly')) . '/>';
	}

}
