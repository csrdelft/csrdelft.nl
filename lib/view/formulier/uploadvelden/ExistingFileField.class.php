<?php

namespace CsrDelft\view\formulier\uploadvelden;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\Bestand;
use CsrDelft\model\entity\Map;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\SelectField;

/**
 * ExistingFileField.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Ophalen van bestand dat al op de server staat.
 * Bijvoorbeeld na uploaden met sFTP.
 *
 */
class ExistingFileField extends SelectField {

	public $filterMime;
	private $dir;
	private $verplaats;

	public function __construct($name, array $filterMime, Map $root = null) {
		parent::__construct($name, null, 'Uit publieke FTP-map', array());
		$this->filterMime = $filterMime;
		if ($root === null) {
			$this->dir = new Map();
			$this->dir->path = PUBLIC_FTP;
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
		$this->verplaats = new CheckboxField($this->name . '_BV', false, null, 'Bestand verplaatsen');
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
		if (!$this->isAvailable() OR !($this->model instanceof Bestand) OR !$this->model->exists() OR empty($this->model->filesize)) {
			$this->error = 'Bestand is niet (meer) aanwezig';
		} elseif (!empty($this->filterMime) AND !in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->model->mimetype;
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, $overwrite);
		$copied = copy($this->model->directory . $this->model->filename, $directory . $filename);
		if (!$copied) {
			throw new CsrException('Bestand kopieren mislukt: ' . htmlspecialchars($this->model->directory . $this->model->filename));
		}
		if (false === @chmod($directory . $filename, 0644)) {
			throw new CsrException('Geen eigenaar van bestand: ' . htmlspecialchars($directory . $filename));
		}
		if ($this->verplaats->getValue()) {
			$moved = unlink($this->model->directory . $this->model->filename);
			if (!$moved) {
				throw new CsrException('Verplaatsen mislukt: ' . htmlspecialchars($this->model->directory . $this->model->filename));
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
