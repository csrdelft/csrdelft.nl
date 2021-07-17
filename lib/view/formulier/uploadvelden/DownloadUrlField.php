<?php

namespace CsrDelft\view\formulier\uploadvelden;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\Bestand;
use CsrDelft\view\formulier\invoervelden\UrlField;
use CsrDelft\view\formulier\UrlDownloader;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Een bestand downloaden van een url, met file_get_contents of de
 * cURL-extensie. Als beide niet beschikbaar zijn wordt het formulier-
 * element niet weergegeven.
 *
 */
class DownloadUrlField extends UploaderField {

	public $filterMime;
	private $downloader;
	private $tmpFile;

	public function __construct($name, array $filterMime) {
		parent::__construct($name, 'https://', 'Downloaden van URL');
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
			$urlName = substr(trim($this->value), strrpos($this->value, '/') + 1);
			$cleanName = preg_replace('/[^a-zA-Z0-9\s\.\-\_]/', '', $urlName);
			$this->tmpFile = TMP_PATH . $cleanName;
			if (!is_writable(TMP_PATH)) {
				throw new CsrException('TMP_PATH is niet beschrijfbaar');
			}
			$filesize = file_put_contents($this->tmpFile, $data);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $this->tmpFile);
			finfo_close($finfo);
			if (in_array($mimetype, Afbeelding::$mimeTypes)) {
				$this->model = new Afbeelding($this->tmpFile);
			} else {
				$this->model = new Bestand();
				$this->model->filename = $cleanName;
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
		} elseif (!$this->model instanceof Bestand || !$this->model->exists() || empty($this->model->filesize)) {
			$error = error_get_last();
			$this->error = $error['message'];
		} elseif (!empty($this->filterMime) && !in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . $this->model->mimetype;
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, $overwrite);
		$copied = copy(join_paths($this->model->directory, $this->model->filename), join_paths($directory, $filename));
		if (!$copied) {
			throw new CsrException('Bestand kopieren mislukt: ' . htmlspecialchars(join_paths($this->model->directory, $this->model->filename)));
		}
		$moved = unlink(join_paths($this->model->directory, $this->model->filename));
		if (!$moved) {
			throw new CsrException('Verplaatsen mislukt: ' . htmlspecialchars(join_paths($this->model->directory, $this->model->filename)));
		}
		if (false === @chmod(join_paths($directory, $filename), 0644)) {
			throw new CsrException('Geen eigenaar van bestand: ' . htmlspecialchars(join_paths($directory, $filename)));
		}
		$this->model->directory = $directory;
		$this->model->filename = $filename;
	}

	public function getHtml() {
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly')) . '/>';
	}

}
