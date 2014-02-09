<?php

require_once 'MVC/view/Validator.interface.php';
require_once 'MVC/view/form/FormElement.abstract.php';

/**
 * Formulier.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Voorbeeld:
 *
 * $form=new Formulier(
 * 		'formulier-ID',
 * 		'/index.php',
 * 		array(
 * 			InputField('naam', '', 'Naam'),
 * 			PassField('password'),
 * 			SubmitButton('save')
 * 		);
 * 
 * Alle dingen die we in de field-array van een Formulier stoppen
 * moeten een uitbreiding zijn van FormElement.
 *
 * @see FormElement
 */
class Formulier implements View, Validator {

	private $formId;
	private $action;
	/** @var FormElement[] */
	private $fields;
	public $css_classes;
	public $error = '';

	public function __construct($formId, $action = null, $fields = array()) {
		$this->formId = $formId;
		$this->action = $action;
		$this->fields = $fields;
		$this->css_classes = array('Formulier');
	}

	public function getModel() {
		return $this;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function getAction() {
		return $this->action;
	}

	public function getFormId() {
		return $this->formId;
	}

	public function getFields() {
		return $this->fields;
	}

	public function addFields($fields) {
		$this->fields = array_merge($this->fields, $fields);
	}

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted() {
		$posted = false;
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField AND $field->isPosted()) {
				$posted = true;
			}
		}
		return $posted;
	}

	/**
	 * Geeft waardes van de formuliervelden terug
	 */
	public function getValues() {
		$values = array();
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField) {
				$values[$field->getName()] = $field->getValue();
			}
		}
		return $values;
	}

	/**
	 * Alle valideer-functies kunnen het model gebruiken dat meegegeven is
	 * bij constructie van het InputField om tegen te valideren.
	 */
	public function validate() {
		if (!$this->isPosted()) {
			$this->error = 'Formulier is niet compleet';
			return false;
		}
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid = true;
		foreach ($this->getFields() as $field) {
			//we checken alleen de InputFields, niet de comments enzo.
			if ($field instanceof InputField AND !$field->validate()) {
				$valid = false;
			}
		}
		return $valid;
	}

	public function getError() {
		return $this->error;
	}

	/**
	 * Zoekt een InputField met de gegeven naam
	 *
	 * @param string $fieldname
	 * @return bool|InputField
	 */
	public function findByName($fieldname) {
		foreach ($this->fields as $field) {
			//we checken alleen de InputFields, niet de comments enzo.
			if ($field instanceof InputField AND $field->getName() == $fieldname) {
				return $field;
			}
		}
		return false;
	}

	/**
	 * Poept het formulier uit, inclusief <form>-tag, en de javascript
	 * voor de autocomplete...
	 */
	public function view($compleetformulier = true) {
		if ($compleetformulier) {
			echo '<form';
			if ($this->action != null) {
				echo ' action="' . $this->action . '"';
			}
			echo ' id="' . $this->formId . '" class="' . implode(' ', $this->css_classes) . '" method="post">' . "\n";
			echo '<script type="text/javascript">if(FieldSuggestions==undefined){var FieldSuggestions=[];} </script>';
		}

		$javascript = array();
		foreach ($this->getFields() as $field) {
			if ($compleetformulier) {
				$field->view();
			}
			$js = $field->getJavascript();
			$javascript[md5($js)] = $js . "\n";
		}

		echo '<script type="text/javascript">jQuery(document).ready(function($){' . "\n" . implode($javascript) . "\n" . '});</script>';
		if ($compleetformulier) {
			echo '</form>';
		}
	}

}
