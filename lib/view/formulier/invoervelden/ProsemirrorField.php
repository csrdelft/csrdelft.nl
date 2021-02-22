<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\ProsemirrorToBb;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class ProsemirrorField extends InputField
{
	public function getHtml()
	{
		$attribute = $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly'));
		$converter = ContainerFacade::getContainer()->get(BbToProsemirror::class);
		$jsonValue = htmlentities(json_encode($converter->toProseMirror($this->getValue())));

		return <<<HTML
<input type="hidden" name="{$this->getName()}_type" value="pm">
<input type="hidden" $attribute value="{$jsonValue}">
<div class="pm-editor" data-prosemirror-doc="{$this->getId()}"></div>
HTML;
	}

	public function getValue()
	{
		if ($this->isPosted()) {
			$converter = ContainerFacade::getContainer()->get(ProsemirrorToBb::class);
			$this->value = $converter->convertToBb(filter_input(INPUT_POST, $this->name, FILTER_UNSAFE_RAW));
		}
		return $this->value;
	}
}
