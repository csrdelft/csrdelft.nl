<?php

namespace CsrDelft\view\formulier\uploadvelden;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Bestand;
use CsrDelft\model\entity\Map;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\RadioField;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Dit veld geeft de keuze tussen meerdere uploaders. De gebruiker maakt een keuze door een specifieke radiobutton
 * te kiezen.
 */
class FileField extends RadioField {

	/** @var BestandBehouden  */
	private $behoudenField;
	/** @var UploadFileField  */
	private $uploadField;
	private $urlField;
	protected $uploaders;

	public function __construct($name, $description, Bestand $bestand = null, array $filterMime = array()) {
		$this->behoudenField = new BestandBehouden($name . '_BB', $filterMime, $bestand);
		$this->uploadField = new UploadFileField($name . '_HF', $filterMime);
		$this->urlField = new DownloadUrlField($name . '_DU', $filterMime);
		$this->uploaders = array(
			$this->behoudenField->name => $this->behoudenField,
			$this->uploadField->name => $this->uploadField,
			$this->urlField->name => $this->urlField
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

		$this->wrapperClassName .= 'FileField';
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
			throw new CsrException('Upload method not available: ' . htmlspecialchars($this->value));
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

	public function getOptionHtml($value, $description) {
		$html = '<div class="mb-2">';
		$html .= parent::getOptionHtml($value, $description);
		$html .= $this->uploaders[$value]->getHtml();
		$html .= "</div>";
		return $html;
	}
}
