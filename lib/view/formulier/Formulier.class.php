<?php

require_once 'view/View.interface.php';
require_once 'view/Validator.interface.php';
require_once 'view/formulier/FormElement.abstract.php';
require_once 'view/formulier/InvoerVelden.class.php';
require_once 'view/formulier/GetalVelden.class.php';
require_once 'view/formulier/KeuzeVelden.class.php';
require_once 'view/formulier/BBCodeVelden.class.php';
require_once 'view/formulier/UploadVelden.class.php';
require_once 'view/formulier/FormKnoppen.class.php';

/**
 * Formulier.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Alle dingen die we in de field-array van een Formulier stoppen
 * moeten een uitbreiding zijn van FormElement.
 * 
 * @see FormElement
 */
class Formulier implements View, Validator, FormElement {

	protected $model;
	protected $action;
	private $formId;
	public $post = true;
	private $enctype = 'multipart/form-data';
	private $linkedDataTableId;
	/**
	 * Fields must be added via addFields()
	 * or insertElementBefore() methods,
	 * and retrieved with getFields() method.
	 * 
	 * @var FormElement[]
	 */
	private $fields = array();
	protected $javascript = '';
	public $css_classes = array();
	public $titel;
	public $stappen_submit = false;
	public $nestedForm = false;

	public function __construct($model, $action, $titel = null) {
		$this->model = $model;
		$this->action = $action;
		$this->titel = $titel;
		$this->formId = uniqid(get_class($this->model));
		$this->css_classes[] = 'Formulier';
		$this->linkedDataTableId = filter_input(INPUT_POST, 'DataTableId', FILTER_SANITIZE_STRING);
	}

	public function getFormId() {
		return $this->formId;
	}

	public function getDataTableId() {
		return $this->linkedDataTableId;
	}

	/**
	 * Set the id late (after constructor).
	 * Use in case it is not POSTed.
	 * 
	 * @param string $dataTableId
	 */
	public function setDataTableId($dataTableId) {
		$this->linkedDataTableId = $dataTableId;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getType() {
		return get_class($this);
	}

	private function loadProperty(InputField $field) {
		$fieldName = $field->getName();
		if ($this->model instanceof PersistentEntity AND property_exists($this->model, $fieldName)) {
			$this->model->$fieldName = $field->getValue();
		}
	}

	public function generateFields() {
		if (!$this->model instanceof PersistentEntity) {
			return;
		}
		$fields = array();
		foreach ($this->model->getAttributes() as $fieldName) {
			$definition = $this->model->getAttributeDefinition($fieldName);
			if (!isset($definition[1]) OR $definition[1] === false) {
				$class = 'Required';
			} else {
				$class = '';
			}
			$desc = ucfirst(str_replace('_', ' ', $fieldName));
			switch ($definition[0]) {
				case T::String:
					if (startsWith($fieldName, 'rechten_')) {
						$class .= 'RechtenField';
						break;
					}
				// fall through
				case T::Char:
					if ($fieldName === 'verticale') {
						$class .= 'VerticaleField';
						break;
					}
					$class .= 'TextField';
					break;
				case T::Boolean: $class .= 'JaNeeField';
					break;
				case T::Integer: $class .= 'IntField';
					break;
				case T::Float: $class .= 'FloatField';
					break;
				case T::Date: $class .= 'DateField';
					break;
				case T::Time: $class .= 'TimeField';
					break;
				case T::DateTime: $class .= 'DateTimeField';
					break;
				case T::Text:
				case T::LongText: $class .= 'TextareaField';
					break;
				case T::Enumeration: $class .= 'SelectField';
					break;
				case T::UID: $class .='LidField';
					break;
			}
			if ($definition[0] == T::Enumeration) {
				$options = array();
				foreach ($definition[2]::getTypeOptions() as $option) {
					$options[$option] = $definition[2]::getDescription($option);
				}
				$fields[$fieldName] = new $class($fieldName, $this->model->$fieldName, $desc, $options);
			} elseif ($definition[0] == T::Char) {
				$fields[$fieldName] = new $class($fieldName, $this->model->$fieldName, $desc, 1);
			} else {
				$fields[$fieldName] = new $class($fieldName, $this->model->$fieldName, $desc);
			}
		}
		foreach ($this->model->getPrimaryKey() as $fieldName) {
			$fields[$fieldName]->readonly = true;
			$fields[$fieldName]->hidden = true;
			$fields[$fieldName]->required = false;
		}
		return $fields;
	}

	public function getFields() {
		return $this->fields;
	}

	public function hasFields() {
		return !empty($this->fields);
	}

	/**
	 * Zoekt een InputField met exact de gegeven naam.
	 *
	 * @param string $fieldName
	 * @return InputField OR false if not found
	 */
	public function findByName($fieldName) {
		foreach ($this->fields as $field) {
			if (($field instanceof InputField OR $field instanceof FileField) AND $field->getName() === $fieldName) {
				return $field;
			}
		}
		return false;
	}

	public function addFields(array $fields) {
		foreach ($fields as $field) {
			if ($field instanceof InputField) {
				$this->loadProperty($field);
			} elseif ($field instanceof Formulier) {
				$field->nestedForm = true;
			}
		}
		$this->fields = array_merge($this->fields, $fields);
	}

	public function insertAtPos($pos, FormElement $field) {
		if ($field instanceof InputField) {
			$this->loadProperty($field);
		}
		array_splice($this->fields, $pos, 0, array($field));
	}

	public function removeField(FormElement $field) {
		$pos = array_search($field, $this->fields);
		unset($this->fields[$pos]);
	}

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted() {
		foreach ($this->fields as $field) {
			if ($field instanceof InputField AND ! $field->isPosted()) {
				//setMelding($field->getName() . ' is niet gepost', 2); //DEBUG
				return false;
			}
		}
		return true;
	}

	/**
	 * Alle valideer-functies kunnen het model gebruiken bij het valideren
	 * dat meegegeven is bij de constructie van het InputField.
	 */
	public function validate() {
		if (!$this->isPosted()) {
			return false;
		}
		$valid = true;
		foreach ($this->fields as $field) {
			if ($field instanceof Validator AND ! $field->validate()) { // geen comments bijv.
				$valid = false; // niet gelijk retourneren om voor alle velden eventueel errors te zetten
			}
		}
		if (!$valid) {
			$this->css_classes[] = 'metFouten';
		}
		return $valid;
	}

	/**
	 * Geeft waardes van de formuliervelden terug.
	 */
	public function getValues() {
		$values = array();
		foreach ($this->fields as $field) {
			if ($field instanceof InputField) {
				$values[$field->getName()] = $field->getValue();
			}
		}
		return $values;
	}

	/**
	 * Geeft errors van de formuliervelden terug.
	 */
	public function getError() {
		$errors = array();
		foreach ($this->fields as $field) {
			if ($field instanceof Validator) {
				$fieldName = $field->getName();
				if ($field->getError() !== '') {
					$errors[$fieldName] = $field->getError();
				}
			}
		}
		if (empty($errors)) {
			return null;
		}
		return $errors;
	}

	public function getJavascript() {
		$js = $this->javascript;
		foreach ($this->fields as $field) {
			$js .= $field->getJavascript();
		}
		if ($this->stappen_submit) {
			$js .= <<<JS

$(form).formSteps({submitButton: "{$this->stappen_submit}"});
JS;
		}
		return $js;
	}

	protected function getFormTag() {
		$dataTableId = $this->getDataTableId();
		if ($dataTableId) {
			$this->css_classes[] = 'DataTableResponse';
		}
		return '<form enctype="' . $this->enctype . '" action="' . $this->action . '" id="' . $this->getFormId() . '" data-tableid="' . $dataTableId . '" class="' . implode(' ', $this->css_classes) . '" method="' . ($this->post ? 'POST' : 'GET') . '">';
	}

	protected function getScriptTag() {
		$formId = $this->getFormId();
		return <<<JS
<script type="text/javascript">
/* {$formId} */
$(document).ready(function () {
	var form = document.getElementById('{$formId}');
	{$this->getJavascript()}
});
</script>
JS;
	}

	public function getHtml() {
		throw new Exception('Not implemented');
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 * 
	 * @param boolean $showMelding Toon meldingen bovenaan formulier
	 */
	public function view($showMelding = true) {
		if ($showMelding) {
			echo getMelding();
		}
		if ($this->nestedForm) {
			echo '<div id="' . $this->getFormId() . '" class="' . implode(' ', $this->css_classes) . '">';
		} else {
			echo $this->getFormTag();
		}
		$titel = $this->getTitel();
		if (!empty($titel)) {
			echo '<h2 class="Titel">' . $titel . '</h2>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->fields as $field) {
			$field->view();
		}
		echo $this->getScriptTag();
		if ($this->nestedForm) {
			echo '</div>';
		} else {
			echo '</form>';
		}
	}

	/**
	 * Geef een array terug van de gewijzigde velden.
	 *
	 * @returns ChangeLogEntry
	 */
	public function diff() {
		require_once 'model/ChangeLogModel.class.php';
		$diff = array();
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField) {
				$old = $field->getOrigValue();
				$new = $field->getValue();
				if ($old !== $new) {
					$prop = $field->getName();
					$diff[$prop] = ChangeLogModel::instance()->nieuw($this->getModel(), $prop, $old, $new);
				}
			}
		}
		return $diff;
	}

	/**
	 * Maak een stukje bbcode aan met daarin de huidige wijziging, door wie en wanneer.
	 * 
	 * @param ChangeLogEntry[] $diff
	 * @return string
	 */
	public function changelog(array $diff) {
		$changelog = '';
		if (!empty($diff)) {
			$changelog .= '[div]Bewerking van [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][br]';
			foreach ($diff as $change) {
				$changelog .= '(' . $change->property . ') ' . $change->old_value . ' => ' . $change->new_value . '[br]';
			}
			$changelog .= '[/div][hr]';
		}
		return $changelog;
	}

}

/**
 * Form as modal content.
 */
class ModalForm extends Formulier {

	public function view() {
		$this->css_classes[] = 'ModalForm';
		echo '<div id="modal" class="modal-content outer-shadow dragobject" tabindex="-1" style="display: block;">';
		parent::view();
		printDebug();
		echo '</div>';
	}

}

/**
 * InlineForm with single InputField and FormDefaultKnoppen.
 */
abstract class InlineForm extends Formulier {

	private $field;
	private $toggle;

	public function __construct($model, $action, InputField $field, $toggle = true, $buttons = false) {
		parent::__construct($model, $action);
		$this->css_classes[] = 'InlineForm';
		$this->css_classes[] = $this->getType();
		$this->field = $field;
		$this->toggle = $toggle;

		$fields = array();
		$fields[] = $this->field;

		if ($buttons instanceof FormKnoppen) {
			$fields[] = $buttons;
		} elseif ($buttons) {
			$fields[] = new FormDefaultKnoppen(null, false, true, false, true, false, false);
		} else {
			$this->field->enter_submit = true;
			$this->field->escape_cancel = true;
		}
		if (!isset($this->field->title) AND property_exists($this->field, 'description')) {
			$this->field->title = $this->field->description;
		}

		$this->addFields($fields);
	}

	public function getFormId() {
		if (isset($_POST['InlineFormId'])) {
			return filter_input(INPUT_POST, 'InlineFormId', FILTER_SANITIZE_STRING);
		}
		return parent::getFormId();
	}

	public function getHtml() {
		$formId = $this->getFormId();
		$html = '<div id="wrapper_' . $formId . '" class="InlineForm">';
		if ($this->toggle) {
			$html .= '<div id="toggle_' . $formId . '" class="InlineFormToggle">' . $this->field->getValue() . '</div>';
			$this->css_classes[] = 'ToggleForm';
		}
		$html .= $this->getFormTag();
		foreach ($this->getFields() as $field) {
			$html .= $field->getHtml();
		}
		$html .= $this->getScriptTag();
		return $html . '</form></div>';
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getField() {
		return $this->field;
	}

}
