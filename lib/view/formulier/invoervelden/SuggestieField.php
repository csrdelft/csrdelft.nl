<?php


namespace CsrDelft\view\formulier\invoervelden;


class SuggestieField extends TextField
{
	public $suggesties = [];

	public function __construct($name, $value, $description, $suggesties = [], $max_len = 255, $min_len = 0, $model = null)
	{
		parent::__construct($name, $value, $description, $max_len, $min_len, $model);

		$this->suggesties = $suggesties;
	}

	public function getInputAttribute($attribute)
	{
		if ($attribute == 'list') {
			return "list=\"dl_{$this->getId()}\"";
		}

		return parent::getInputAttribute($attribute);
	}

	public function getHtml()
	{
		$html = '<input ' . $this->getInputAttribute(array('type', 'id', 'list', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete')) . ' />';

		$html .= "<datalist id=\"dl_{$this->getId()}\">";
		foreach ($this->suggesties as $suggestie) {
			$html .= "<option value=\"$suggestie\">";
		}
		$html .= "</datalist>";

		return $html;
	}

}
