<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\BbToProsemirror;
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
class BBCodeField extends InputField
{

	public function __construct($name, $value, $description)
	{
		parent::__construct($name, $value, $description);
	}

	public function getPreviewDiv()
	{
		return '';
	}

	public function getRenderType()
	{
		return filter_input(INPUT_POST, $this->getName() . '_type');
	}

	public function getHtml()
	{
		$inputAttribute = $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly', 'placeholder', 'maxlength', 'rows', 'autocomplete'));
		$converter = ContainerFacade::getContainer()->get(BbToProsemirror::class);
		$jsonValue = json_encode($converter->toProseMirror($this->getValue()));

		return <<<HTML
<input type="hidden" name="{$this->getName()}_type" value="pm">
<input type="hidden" $inputAttribute value="{$jsonValue}">
<div class="pm-editor" data-prosemirror-doc="{$this->getId()}"></div>
HTML;
	}

	public function getValue()
	{
		if ($this->isPosted()) {
			$converter = ContainerFacade::getContainer()->get(ProsemirrorToBb::class);
			$this->value = $converter->render(filter_input(INPUT_POST, $this->name, FILTER_UNSAFE_RAW));
		}
		return $this->value;
	}
}
