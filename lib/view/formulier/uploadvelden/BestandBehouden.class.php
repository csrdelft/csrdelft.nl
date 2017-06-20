<?php
namespace CsrDelft\view\formulier\uploadvelden;
use function CsrDelft\format_filesize;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\Bestand;
use CsrDelft\view\formulier\invoervelden\InputField;
use Exception;

/**
 * BestandBehouden.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Bestaand bestand behouden.
 *
 * @property Bestand $model
 *
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
		} elseif (!empty($this->filterMime) AND !in_array($this->model->mimetype, $this->filterMime)) {
			$this->error = 'Bestandstype niet toegestaan: ' . htmlspecialchars($this->model->mimetype);
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false) {
		parent::opslaan($directory, $filename, $overwrite);
		if (false === @chmod($this->model->directory . $this->model->filename, 0644)) {
			throw new Exception('Geen eigenaar van bestand: ' . htmlspecialchars($this->model->directory . $this->model->filename));
		}
	}

	public function getHtml() {
		return '<div ' . $this->getInputAttribute(array('id', 'name', 'class')) . '>' . $this->model->filename . ' (' . format_filesize($this->model->filesize) . ')</div>';
	}

	public function getPreviewDiv() {
		if ($this->model instanceof Afbeelding) {
			return '<div id="imagePreview_' . $this->getId() . '" class="previewDiv"><img src="' . str_replace(PHOTOS_PATH, CSR_ROOT . '/plaetjes/', $this->model->directory) . $this->model->filename . '" width="' . $this->model->width . '" height="' . $this->model->height . '" /></div>';
		}
		return '';
	}

}
