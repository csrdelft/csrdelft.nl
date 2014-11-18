<?php

require_once 'MVC/view/View.interface.php';
require_once 'MVC/view/Validator.interface.php';
require_once 'MVC/view/formulier/FormElement.abstract.php';
require_once 'MVC/view/formulier/InvoerVelden.class.php';
require_once 'MVC/view/formulier/KeuzeVelden.class.php';
require_once 'MVC/view/formulier/UploadVelden.class.php';
require_once 'MVC/view/formulier/FormKnoppen.class.php';

/**
 * Formulier.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Voorbeeld:
 *
 * $form = new Formulier(
 * 		$model,
 * 		'formulier-ID',
 * 		'/example.php',
 * 		array(
 * 			InputField('naam', $value, 'Naam'),
 * 			SubmitResetCancel()
 * 		);
 * 
 * Alle dingen die we in de field-array van een Formulier stoppen
 * moeten een uitbreiding zijn van FormElement.
 *
 * @see FormElement
 */
class Formulier implements View, Validator {

	protected $model;
	protected $formId;
	protected $action = null;
	private $enctype = 'multipart/form-data';
	/**
	 * Fields must be added via addFields()
	 * or insertElementBefore() methods,
	 * and retrieved with getFields() method.
	 * 
	 * @var FormElement[]
	 */
	private $fields = array();
	protected $css_classes = array();
	public $titel;

	public function __construct($model, $formId, $action, $titel = false) {
		$this->model = $model;
		$this->formId = $formId;
		$this->action = $action;
		$this->titel = $titel;
		$this->css_classes[] = 'Formulier';
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

	public function loadProperty(InputField $field) {
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
			if (isset($definition[1]) AND $definition[1] === true) {
				$class = '';
			} else {
				$class = 'Required';
			}
			$desc = ucfirst(str_replace('_', ' ', $fieldName));
			switch ($definition[0]) {
				case T::String:
					$class .= 'TextField';
					break;
				case T::Boolean:
					$class .= 'VinkField';
					break;
				case T::Integer:
					$class .= 'IntField';
					break;
				case T::Float:
					$class .= 'DecimalField';
					break;
				case T::DateTime:
					$class .= 'DatumField';
					break;
				case T::Text:
					$class .= 'TextareaField';
					break;
				case T::LongText:
					$class .= 'TextareaField';
					break;
				case T::Enumeration:
					$class .= 'SelectField';
					$fields[$fieldName] = $class($fieldName, $this->model->$fieldName, $desc, $definition[3]);
					continue;
				case T::UID:
					$class .='LidField';
					break;
			}
			$fields[$fieldName] = new $class($fieldName, $this->model->$fieldName, $desc);
		}
		foreach ($this->model->getPrimaryKey() as $fieldName) {
			$fields[$fieldName]->readonly = true;
		}
		$this->addFields($fields);
	}

	public function getFields() {
		return $this->fields;
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

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted() {
		foreach ($this->fields as $field) {
			if (($field instanceof InputField OR $field instanceof FileField) AND ! $field->isPosted()) {
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
				$fieldName = $field->getName();
				$values[$fieldName] = $field->getValue();
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
				$errors[$fieldName] = $field->getError();
			}
		}
		if (empty($errors)) {
			return null;
		}
		return $errors;
	}

	public function getJavascript() {
		$javascript = array();
		foreach ($this->fields as $field) {
			$js = $field->getJavascript();
			$javascript[md5($js)] = $js;
		}
		return implode("\n", $javascript);
	}

	public function getFormTag() {
		return '<form enctype="' . $this->enctype . '" action="' . $this->action . '" id="' . $this->formId . '" class="' . implode(' ', $this->css_classes) . '" method="post">';
	}

	public function getScriptTag() {
		$id = str_replace('-', '_', $this->formId);
		return <<<JS
<script type="text/javascript">function form_ready_{$id}() {
	var form = document.getElementById('{$this->formId}');
	{$this->getJavascript()}
}
</script>
JS;
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 */
	public function view() {
		echo getMelding();
		if ($this->getTitel()) {
			echo '<h1 class="formTitle">' . $this->getTitel() . '</h1>';
		}
		echo $this->getFormTag();
		foreach ($this->fields as $field) {
			$field->view();
		}
		echo $this->getScriptTag();
		echo '</form>';
	}

}

/**
 * Formulier as modal content.
 */
class ModalForm extends Formulier {

	public function view() {
		$this->css_classes[] = 'modal';
		echo '<div id="modal-content">';
		echo parent::view();
		echo '</div>';
	}

}

/**
 * InlineForm with single InputField and SubmitResetCancel.
 */
class InlineForm extends Formulier {

	public function __construct($model, $formId, $action, InputField $field, $label = false) {
		parent::__construct($model, $formId, $action);

		$fields = array();
		$fields['input'] = $field;
		$fields['btn'] = new FormDefaultKnoppen(null, false, true, $label, true);

		$this->addFields($fields);
	}

	public function view() {
		$this->css_classes[] = 'InlineForm';
		$fields = $this->getFields();
		echo '<div id="InlineForm-' . $this->formId . '">';
		echo $this->getFormTag();
		echo $fields['input']->view();
		echo '<div class="InlineFormToggle">' . $fields['input']->getValue() . '</div>';
		$fields['btn']->view();
		echo $this->getScriptTag();
		echo '</form></div>';
	}

	public function getValue() {
		$fields = $this->getFields();
		return $fields['input']->getValue();
	}

}
