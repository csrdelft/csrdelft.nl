<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\BbToProsemirror;

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
		$converter = ContainerFacade::getContainer()->get(BbToProsemirror::class);
		$jsonValue = htmlspecialchars(json_encode($converter->toProseMirror($this->value)));
		return  <<<HTML
<input type="hidden" $inputAttribute value="{$jsonValue}">
<div id="editor"></div>
HTML
		. ($this->preview ? <<<HTML
	<div class="col-auto">
		<a class="btn btn-secondary" data-bbpreview-btn="{$this->getId()}" href="#" title="Toon voorbeeld met opmaak">Voorbeeld</a>
	</div>
HTML
				: ''). <<<HTML
</div>
HTML;
	}
}
