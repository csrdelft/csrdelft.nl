<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/03/2019
 */
class CheckboxesField extends InputField {
	/**
	 * @var array
	 */
	private $opties;

	public function __construct($name, $value, $description, array $opties) {
		$this->opties = $opties;

		parent::__construct($name, $value, $description, null);
	}


	public function getHtml() {
		$html = '';
		foreach ($this->opties as $value => $description) {
			$checkboxId = $this->name . '_' . $value;
			$checked = in_array($value, $this->value) ? 'checked="checked" ' : '';
			$html .= <<<HTML
<div class="form-check form-check-inline">
	<input type="hidden" name="{$checkboxId}" value="false"/>
	<input class="form-check-input" type="checkbox" id="{$checkboxId}" name="{$checkboxId}" value="{$value}" {$checked}/>
	<label class="form-check-label" for="{$checkboxId}">{$description}</label>
</div>
HTML;
		}

		return $html;
	}

	public function isPosted() {
		foreach ($this->opties as $value => $description) {
			if (!isset($_POST[$this->name . '_' . $value])) return false;
		}

		return true;
	}

	public function getValue() {
		if ($this->isPosted()) {
			$values = [];

			foreach ($this->opties as $value => $description) {
				$selection = filter_input(INPUT_POST, $this->name . '_' . $value);
				if ($selection != 'false') {
					$values[] = $value;
				}
			}

			$this->value = $values;
		}

		return $this->value;
	}

}
