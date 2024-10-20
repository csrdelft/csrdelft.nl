<?php

namespace CsrDelft\view\formulier;

use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormKnoppen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 *
 * InlineForm with single InputField and FormDefaultKnoppen.
 */
abstract class InlineForm extends Formulier implements FormElement
{
	public function __construct(
		$model,
		$action,
		private readonly InputField $field,
		private $toggle = true,
		$buttons = false,
		$dataTableId = false
	) {
		parent::__construct($model, $action, null, $dataTableId);
		if (isset($_POST['InlineFormId'])) {
			$this->formId = filter_input(
				INPUT_POST,
				'InlineFormId',
				FILTER_SANITIZE_STRING
			);
		}
		$this->css_classes[] = 'InlineForm';
		$this->css_classes[] = $this->getType();

		$fields = [];
		$fields[] = $this->field;

		if ($buttons instanceof FormKnoppen) {
			$fields[] = $buttons;
		} elseif ($buttons) {
			$fields[] = new FormDefaultKnoppen(
				null,
				false,
				true,
				false,
				true,
				false,
				$dataTableId
			);
		} else {
			$this->field->enter_submit = true;
			$this->field->escape_cancel = true;
		}
		if ($this->field->title === null) {
			$this->field->title = $this->field->description;
		}

		$this->addFields($fields);
	}

	public function getHtml()
	{
		$html = '<div id="wrapper_' . $this->formId . '" class="InlineForm">';
		if ($this->toggle) {
			$value =
				$this->field->getValue() ?? '<div class="text-muted">Geen waarde</div>';
			$html .=
				'<div id="toggle_' .
				$this->formId .
				'" class="InlineFormToggle">' .
				$value .
				'</div>';
			$this->css_classes[] = 'ToggleForm';
		}
		$html .= $this->getFormTag();
		foreach ($this->getFields() as $field) {
			$html .= $field->getHtml();
		}
		$html .= $this->getScriptTag();
		return $html . '</form></div>';
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
	}

	public function getField()
	{
		return $this->field;
	}

	public function getType()
	{
		return ReflectionUtil::classNameZonderNamespace(static::class);
	}

	/**
	 * Public for FormElement
	 * @return string
	 */
	public function getJavascript()
	{
		return parent::getJavascript();
	}
}
