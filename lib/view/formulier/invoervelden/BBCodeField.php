<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Bevat de uitbreidingen van TextareaField:
 *
 *  - BBCodeField    Textarea met bbcode voorbeeld
 *
 */
class BBCodeField extends TextareaField {

	public function __construct($name, $value, $description, $rows = 5, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $rows, $max_len, $min_len);
	}

	public function getPreviewDiv() {
		return '<div id="bbcodePreview_' . $this->getId() . '" data-bbpreview="'. $this->getId() . '" class="previewDiv bbcodePreview"></div>';
	}

	public function getHtml() {
		return parent::getHtml() . <<<HTML

<div class="row justify-content-end">
	<div class="col-auto">
		<a class="btn btn-light" href="/wiki/cie:diensten:forum" target="_blank" title="Ga naar het overzicht van alle opmaak codes">Opmaakhulp</a>
	</div>
	<div class="col-auto">
		<a class="btn btn-secondary" data-bbpreview-btn="{$this->getId()}" href="#" title="Toon voorbeeld met opmaak">Voorbeeld</a>
	</div>
</div>
HTML;
	}
}
