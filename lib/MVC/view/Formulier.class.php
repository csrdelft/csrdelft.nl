<?php

require_once 'MVC/view/FormElement.abstract.php';

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
	protected $action;
	/**
	 * Fields must be added via addFields()
	 * or insertElementBefore() methods,
	 * and retrieved with getFields() method.
	 * 
	 * @var FormElement[]
	 */
	private $fields = array();
	public $css_classes = array();
	public $error = '';

	public function __construct($model, $formId, $action = null, $fields = array()) {
		$this->model = $model;
		$this->formId = $formId;
		$this->action = $action;
		$this->css_classes[] = 'Formulier';
		$this->addFields($fields);
	}

	public function getModel() {
		$this->getValues(); // fetch POST values
		return $this->model;
	}

	public function getFormId() {
		return $this->formId;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getAction() {
		return $this->action;
	}

	public function getFields() {
		return $this->fields;
	}

	/**
	 * Zoekt een FormElement met exact de gegeven naam.
	 *
	 * @param string $fieldName
	 * @return InputField OR false if not found
	 */
	public function findByName($fieldName) {
		foreach ($this->fields as $field) {
			if ($field->getName() === $fieldName) {
				return $field;
			}
		}
		return false;
	}

	/**
	 * Fetches POST values itself.
	 * 
	 * @param array $fields
	 */
	public function addFields(array $fields) {
		$this->fields = array_merge($this->fields, $fields);
		$this->getValues(); // fetch POST values
	}

	public function insertElementBefore($fieldName, FormElement $elmnt) {
		$pos = 0;
		foreach ($this->fields as $field) {
			if ($field->getName() === $fieldName) {
				array_splice($this->fields, $pos, 0, $elmnt);
			}
			$pos++;
		}
	}

	/**
	 * Create model input fields.
	 */
	public function generateFields() {
		$fields = array();
		foreach ($this->model->getFormFields() as $propName => $arguments) {
			$fields[] = $this->createFormElement($propName, $arguments);
		}
		$this->addFields($fields);
	}

	/**
	 * Factory to create FormElements using reflection.
	 * 
	 * @param string $propName
	 * @param array $arguments
	 * @return object class instance
	 */
	public function createFormElement($propName, $arguments = array()) {
		$className = $arguments[0];
		$mouseOver = $arguments[1];
		$arguments[0] = $propName; // name of the model property
		$arguments[1] = $this->model->$propName; // value of the model property
		array_splice($arguments, 2, 0, ucfirst(str_replace('_', ' ', $propName))); // label of the field
		$field = construct_instance($className, $arguments);
		$field->title = $mouseOver;
		return $field;
	}

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted() {
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField AND !($field->isPosted() OR $field instanceof VinkField)) {
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
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField AND !$field->validate()) { // geen comments bijv.
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
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField) {
				$propName = $field->getName();
				$values[$propName] = $field->getValue();
				if (isset($this->model) AND property_exists($this->model, $propName)) {
					$this->model->$propName = $values[$propName];
				}
			}
		}
		return $values;
	}

	public function getError() {
		return $this->error;
	}

	public function getJavascript() {
		$javascript = array();
		foreach ($this->getFields() as $field) {
			$js = $field->getJavascript();
			$javascript[md5($js)] = $js;
		}
		return '<script type="text/javascript">$(document).ready(function(){' . "\n" . implode("\n", $javascript) . "\n" . '});</script>';
	}

	/**
	 * Toont het formulier en javascript van alle fields
	 */
	public function view() {
		echo '<form';
		if ($this->getAction() != null) {
			echo ' action="' . $this->getAction() . '"';
		}
		echo ' id="' . $this->getFormId() . '" class="' . implode(' ', $this->css_classes) . '" method="post">' . "\n";
		$addButtons = true;
		foreach ($this->getFields() as $field) {
			$field->view();
			if ($field instanceof SubmitResetCancel) {
				$addButtons = false;
			}
		}
		if ($addButtons) {
			$buttons = new SubmitResetCancel();
			$this->fields[] = $buttons;
			echo $buttons->view();
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
		echo '<div id="popup-content"><h1>' . $this->getTitel() . '</h1>';
		echo parent::view();
		echo '</div>';
	}

}

/**
 * InlineForm with single InputField
 */
class InlineForm extends Formulier {

	public function view($tekst = false) {
		echo '<div id="inline-' . $this->getFormId() . '">';
		echo '<form id="' . $this->getFormId() . '" action="' . $this->getAction() . '" method="post" class="Formulier InlineForm">';
		echo $this->fields[0]->view();
		echo '<div class="FormToggle">' . $this->fields[0]->getValue() . '</div>';
		echo '<a class="knop submit" title="Opslaan"><    img width="16" height="16" class="icon" alt="submit" src="' . CSR_PICS . 'famfamfam/accept.png">' . ($tekst ? ' Opslaan ' : '') . '</a>';
		echo '<a class="knop reset cancel" title="Annuleren"><    img width="16" height="16" class="icon" alt="cancel" src="' . CSR_PICS . 'famfamfam/delete.png">' . ($tekst ? ' Annuleren ' : '') . '</a>';
		echo $this->getJavascript();
		echo '</form></div>';
	}

}
