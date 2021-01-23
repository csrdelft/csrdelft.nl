<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\bbcode\ProsemirrorToBb;

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
		return '';
	}

	public function getFormattedValue()
	{
		return (new ProsemirrorToBb())->render(json_decode(htmlspecialchars_decode($this->getValue())));
	}

	public function getHtml() {
		$inputAttribute = $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly', 'placeholder', 'maxlength', 'rows', 'autocomplete'));
		$bb = ContainerFacade::getContainer()->get(CsrBB::class);
		$converter = new BbToProsemirror($bb);
		$jsonValue = htmlspecialchars(json_encode($converter->toProseMirror($this->value)));

		return <<<HTML
<input type="hidden" $inputAttribute value="{$jsonValue}">
<div class="pm-editor" data-prosemirror-doc="{$this->getId()}"></div>
HTML;
	}
}
