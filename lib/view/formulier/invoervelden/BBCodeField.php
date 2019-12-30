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
		return '<div id="preview_' . $this->getId() . '" class="previewDiv bbcodePreview"></div>';
	}

	public function getHtml() {
		$inputAttribute = $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly', 'placeholder', 'maxlength', 'rows', 'autocomplete'));
		return <<<HTML
<textarea data-bbpreview="{$this->getId()}" $inputAttribute>{$this->value}</textarea>
<div class="btn-toolbar justify-content-end">
	<a class="btn btn-light mr-2" href="/wiki/cie:diensten:forum" target="_blank" title="Ga naar het overzicht van alle opmaak codes">Opmaakhulp</a>
HTML
			. ($this->preview ? <<<HTML
	<a class="btn btn-secondary" data-bbpreview-btn="{$this->getId()}" href="#" title="Toon voorbeeld met opmaak">Voorbeeld</a>
HTML
				: '') . <<<HTML
</div>
HTML;
	}

	/**
	 * View die zou moeten werken voor veel velden.
	 */
	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo '<div class="' . $this->fieldClassName . '">';
		if ($this->preview) {
			echo $this->getPreviewDiv();
		}
		echo $this->getHtml();
		echo $this->getErrorDiv();
		echo '</div>';
		echo $this->getHelpDiv();
		echo '</div>';
	}
}
