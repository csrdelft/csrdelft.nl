<?php

require_once 'MVC/view/Validator.interface.php';
require_once 'MVC/view/form/FormElement.abstract.php';

/**
 * Formulier.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * @see FormElement
 */
class Formulier implements View, Validator {

	private $id;
	private $method = 'post';
	private $fields;
	public $action;
	public $css_classes = array('Formulier');

	public function __construct($id, $action = null, array $fields = array()) {
		$this->id = $id;
		$this->action = $action;
		$this->fields = $fields;
	}

	public function getModel() {
		return $this;
	}

	public function getFormId() {
		return $this->id;
	}

	public function getMethod() {
		return $this->method;
	}

	public function setMethod($method) {
		if ($method === 'post' OR $method === 'post') {
			$this->method = $method;
			return true;
		}
		return false;
	}

	/**
	 * Is het gehele formulier gepost?
	 */
	public function isPosted() {
		$posted = true;
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField AND !$field->isPosted()) {
				$posted = false;
			}
		}
		return $posted;
	}

	public function addField(InputField $field) {
		$this->fields[] = $field;
	}

	public function addFields(array $fields) {
		$this->fields = array_merge($this->fields, $fields);
	}

	public function getFields() {
		return $this->fields;
	}

	/**
	 * Geeft waardes van de formuliervelden terug.
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
	 * Heeft het formulier een error?
	 */
	public function getError() {
		$error = array();
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField) {
				if ($field->getErrorDiv() !== '') {
					$error[] = $field->getErrorDiv();
				}
			}
		}
		return $error;
	}

	/**
	 * Alle valid-functies krijgen een argument mee, wat kan wisselen per
	 * gemaakt formulier.
	 */
	public function validate($extra = null) {
		if (!$this->isPosted()) {
			$this->error = 'Formulier is niet compleet';
			return false;
		}
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid = true;
		foreach ($this->getFields() as $field) {
			//we checken alleen de formfields, niet de comments enzo.
			if ($field instanceof InputField AND !$field->validate($extra)) {
				$valid = false;
			}
		}
		return $valid;
	}

	public function getFieldByName($fieldname) {
		foreach ($this->fields as $field) {
			if ($field->getName() == $fieldname) {
				return $field;
			}
		}
		return false;
	}

	/**
	 * Displays form including javascript for autocomplete.
	 */
	public function view() {
		echo '<form id="' . $this->id . '" action="' . $this->action . '" method="' . $this->method . '"class="' . implode(' ', $this->css_classes) . '">';
		$javascript = array();
		foreach ($this->getFields() as $field) {
			$field->view();
			$javascript[] = $field->getJavascript();
		}
		$javascript[] = 'if(FieldSuggestions==undefined){var FieldSuggestions=[];}';
		echo '<script type="text/javascript">jQuery(document).ready(function($){' . "\n" . implode("\n", $javascript) . "\n" . '});</script>';
		echo '</form>';
	}

}
