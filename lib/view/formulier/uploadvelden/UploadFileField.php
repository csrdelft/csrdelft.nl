<?php

namespace CsrDelft\view\formulier\uploadvelden;

use CsrDelft\common\CsrException;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\Bestand;
use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 * Uploaden van bestand in de browser over http(s).
 */
class UploadFileField extends InputField
{
	public $filterMime;
	public $type = 'file';

	public function __construct($name, array $filterMime)
	{
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

	public function isPosted()
	{
		return isset($_FILES[$this->name]);
	}

	public function isAvailable()
	{
		return true;
	}

	public function getFilter()
	{
		return $this->filterMime;
	}

	public function validate()
	{
		parent::validate();
		if ($this->value['error'] == UPLOAD_ERR_NO_FILE) {
			if ($this->required) {
				$this->error = 'Selecteer een bestand';
			}
		} elseif ($this->value['error'] == UPLOAD_ERR_INI_SIZE) {
			$this->error =
				'Bestand is te groot: Maximaal ' .
				FileUtil::format_filesize(FileUtil::getMaximumFileUploadSize());
		} elseif ($this->value['error'] != UPLOAD_ERR_OK) {
			$this->error = 'Upload-error: code ' . $this->value['error'];
		} elseif (
			!is_uploaded_file($this->value['tmp_name']) or
			empty($this->model->filesize)
		) {
			$this->error =
				'Bestand bestaat niet (meer): ' .
				htmlspecialchars($this->value['tmp_name']);
		} elseif (
			!empty($this->filterMime) and
			!in_array($this->model->mimetype, $this->filterMime)
		) {
			$this->error =
				'Bestandstype niet toegestaan: ' .
				htmlspecialchars($this->model->mimetype);
		} elseif (
			!FileUtil::checkMimetype($this->model->filename, $this->model->mimetype)
		) {
			$this->error =
				'Bestandstype komt niet overeen met bestandsnaam: ' .
				$this->model->mimetype;
		}
		return $this->error === '';
	}

	public function opslaan($directory, $filename, $overwrite = false)
	{
		parent::opslaan($directory, $filename, $overwrite);
		$moved = @move_uploaded_file(
			$this->value['tmp_name'],
			PathUtil::join_paths($directory, $filename)
		);
		if (!$moved) {
			throw new CsrException(
				'Verplaatsen mislukt: ' . htmlspecialchars($this->value['tmp_name'])
			);
		}
		if (false === @chmod(PathUtil::join_paths($directory, $filename), 0644)) {
			throw new CsrException(
				'Geen eigenaar van bestand: ' .
					htmlspecialchars(PathUtil::join_paths($directory, $filename))
			);
		}
		$this->model->directory = $directory;
		$this->model->filename = $filename;
	}

	public function getHtml()
	{
		// werkomheen onbekende mime-types voor client
		if ($this->filterMime == Afbeelding::$mimeTypes) {
			$accept = 'image/*';
		} else {
			$accept = implode('|', $this->filterMime);
		}
		return '<input ' .
			$this->getInputAttribute([
				'type',
				'id',
				'name',
				'class',
				'disabled',
				'readonly',
			]) .
			' accept="' .
			$accept .
			'" data-max-size="' .
			FileUtil::getMaximumFileUploadSize() .
			'" />';
	}

	public function getJavascript()
	{
		$max = FileUtil::getMaximumFileUploadSize();
		$format = FileUtil::format_filesize($max);
		return parent::getJavascript() .
			<<<JS

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
