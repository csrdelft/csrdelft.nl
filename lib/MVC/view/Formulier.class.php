<?php

require_once 'MVC/view/View.interface.php';
require_once 'MVC/view/Validator.interface.php';
require_once 'MVC/view/FormElement.abstract.php';
require_once 'MVC/view/FileField.class.php';

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
	public $titel = '';
	protected $action = null;
	public $enctype = null;
	/**
	 * Fields must be added via addFields()
	 * or insertElementBefore() methods,
	 * and retrieved with getFields() method.
	 * 
	 * @var FormElement[]
	 */
	private $fields = array();
	protected $css_classes = array();
	public $error = '';

	public function __construct($model, $formId, $action = null, array $fields = array()) {
		$this->model = $model;
		$this->formId = $formId;
		$this->action = $action;
		$this->css_classes[] = 'Formulier';
		$this->addFields($fields);
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getModel() {
		return $this->model;
	}

	public function loadProperty(InputField $field) {
		$fieldName = $field->getName();
		if ($this->model instanceof PersistentEntity AND property_exists($this->model, $fieldName)) {
			$this->model->$fieldName = $field->getValue();
		}
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
			if ($field instanceof InputField AND $field->getName() === $fieldName) {
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

	public function insertAfter(FormElement $field, $fieldName) {
		$pos = 0;
		foreach ($this->fields as $after) {
			$pos++;
			if ($after->getName() === $fieldName) {
				if ($field instanceof InputField) {
					$this->loadProperty($field);
				}
				array_splice($this->fields, $pos, 0, $field);
				return true;
			}
		}
		return false;
	}

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted() {
		foreach ($this->fields as $field) {
			if ($field instanceof InputField AND ! ($field->isPosted() OR $field instanceof VinkField)) {
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
			$this->error = 'Formulier is niet compleet';
			return false;
		}
		$valid = true;
		foreach ($this->fields as $field) {
			if ($field instanceof Validator AND ! $field->validate()) { // geen comments bijv.
				$valid = false; // niet gelijk retourneren om voor alle velden eventueel errors te zetten
			}
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

	public function getError() {
		return $this->error;
	}

	public function getJavascript() {
		$javascript = array();
		foreach ($this->fields as $field) {
			$js = $field->getJavascript();
			$javascript[md5($js)] = $js;
		}
		return '<script type="text/javascript">function form_ready_' . str_replace('-', '_', $this->formId) . '(){' . "\n" . implode("\n", $javascript) . "\n" . '}</script>';
	}

	public function getTitleTag() {
		if ($this->titel === '') {
			return '';
		} else {
			return '<h1 class="formTitle">' . $this->titel . '</h1>';
		}
	}

	public function getFormTag() {
		return '<form enctype="multipart/form-data" action="' . $this->action . '" id="' . $this->formId . '" class="' . implode(' ', $this->css_classes) . '" method="post">';
	}

	/**
	 * Toont het formulier en javascript van alle fields
	 */
	public function view() {
		echo SimpleHtml::getMelding();
		echo $this->getTitleTag();
		echo $this->getFormTag();
		foreach ($this->fields as $field) {
			$field->view();
		}
		echo $this->getJavascript();
		echo '</form>';
	}

}

/**
 * Formulier as popup content
 */
class PopupForm extends Formulier {

	public function view() {
		$this->css_classes[] = 'popup';
		echo '<div id="popup-content">';
		echo parent::view();
		echo '</div>';
	}

}

/**
 * InlineForm with single InputField and SubmitResetCancel
 */
class InlineForm extends Formulier {

	public function __construct($model, $formId, $action, InputField $field, $tekst = false) {
		parent::__construct($model, $formId, $action);

		$fields = array();
		$fields['input'] = $field;
		$fields['src'] = new SubmitResetCancel(null, true, $tekst, false);
		$fields['src']->submitIcon = 'accept';
		$fields['src']->cancelReset = true;

		$this->addFields($fields);
	}

	public function view() {
		$this->css_classes[] = 'InlineForm';
		$fields = $this->getFields();
		echo '<div id="InlineForm-' . $this->formId . '">';
		echo $this->getFormTag();
		echo $fields['input']->view();
		echo '<div class="InlineFormToggle">' . $fields['input']->getValue() . '</div>';
		$fields['src']->view();
		echo $this->getJavascript();
		echo '</form></div>';
	}

	public function getValue() {
		$fields = $this->getFields();
		return $fields['input']->getValue();
	}

}
