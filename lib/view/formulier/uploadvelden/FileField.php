<?php

namespace CsrDelft\view\formulier\uploadvelden;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\Bestand;
use CsrDelft\model\entity\Map;
use CsrDelft\view\formulier\keuzevelden\RadioField;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class FileField extends RadioField
{
	private $behoudenField;
	private $uploadField;
	private $urlField;
	protected $uploaders;

	public function __construct(
		$name,
		$description,
		Bestand $bestand = null,
		Map $dir = null,
		array $filterMime = []
	) {
		$this->behoudenField = new BestandBehouden(
			$name . '_BB',
			$filterMime,
			$bestand
		);
		$this->uploadField = new UploadFileField($name . '_HF', $filterMime);
		$this->urlField = new DownloadUrlField($name . '_DU', $filterMime);
		$this->uploaders = [
			$this->behoudenField->name => $this->behoudenField,
			$this->uploadField->name => $this->uploadField,
			$this->urlField->name => $this->urlField,
		];
		$default = null;
		$opties = [];
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

	public function isPosted(): bool
	{
		if (!parent::isPosted()) {
			return false;
		}
		$methode = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_STRING);
		return $this->uploaders[$methode]->isPosted();
	}

	public function getType()
	{
		return $this->value;
	}

	public function getUploader()
	{
		if (!isset($this->uploaders[$this->value])) {
			throw new CsrException(
				'Upload method not available: ' . htmlspecialchars($this->value)
			);
		}
		return $this->uploaders[$this->value];
	}

	public function getFilter()
	{
		return $this->getUploader()->getFilter();
	}

	public function getModel()
	{
		return $this->getUploader()->getModel();
	}

	public function getError()
	{
		return $this->getUploader()->getError();
	}

	public function validate(): bool
	{
		if (!parent::validate()) {
			return false;
		}
		return $this->getUploader()->validate();
	}

	public function opslaan($directory, $filename, $overwrite = false)
	{
		$this->getUploader()->opslaan($directory, $filename, $overwrite);
	}

	public function getOptionHtml($value, $description): string
	{
		$html = '<div class="UploadOptie';
		if ($value === $this->value) {
			$html .= ' verborgen';
		}
		$html .= '">';
		$html .= parent::getOptionHtml($value, $description);
		$html .= '</div><div class="UploadKeuze';
		if ($value !== $this->value) {
			$html .= ' verborgen';
		}
		$html .= '">';
		$html .= $this->uploaders[$value]->getHtml();
		$html .= $this->uploaders[$value]->getPreviewDiv();
		return $html . '</div>';
	}

	public function getJavascript(): string
	{
		$js =
			parent::getJavascript() .
			<<<JS

$('input[name="{$this->name}"]').change(function (event) {
	var aan = $('input[name="{$this->name}"]:checked').parent().parent();
	aan.addClass('verborgen');
	aan.next('.UploadKeuze').slideDown(250);
	var uit = $('input[name="{$this->name}"]').parent().parent().not(aan);
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
