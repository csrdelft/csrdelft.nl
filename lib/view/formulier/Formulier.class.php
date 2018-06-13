<?php

namespace CsrDelft\view\formulier;

use CsrDelft\model\ChangeLogModel;
use CsrDelft\model\entity\ChangeLogEntry;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\formulier\elementen\FormElement;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\knoppen\EmptyFormKnoppen;
use CsrDelft\view\formulier\knoppen\FormKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;
use CsrDelft\view\Validator;
use CsrDelft\view\View;

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
class Formulier implements View, Validator {

	protected $model;
	protected $formId;
	protected $dataTableId;
	protected $action = null;
	public $post = true;
	protected $error;
	private $enctype = 'multipart/form-data';
	public $showMelding = true;
	/**
	 * Fields must be added via addFields()
	 * or insertElementBefore() methods,
	 * and retrieved with getFields() method.
	 *
	 * @var FormElement[]
	 */
	private $fields = array();
	protected $formKnoppen;
	public $css_classes = array();
	protected $javascript = '';
	public $titel;
	public $stappen_submit = false;

	public function __construct($model, $action, $titel = false, $dataTableId = false) {
		$this->model = $model;
		$this->formId = uniqid(classNameZonderNamespace(get_class($this->model == null ? $this : $this->model)));
		$this->action = $action;
		$this->titel = $titel;
		$this->css_classes[] = 'Formulier';
		// Link with DataTable?
		if ($dataTableId === true) {
			$this->dataTableId = filter_input(INPUT_POST, 'DataTableId', FILTER_SANITIZE_STRING);
		} else {
			$this->dataTableId = $dataTableId;
		}

		$this->formKnoppen = new EmptyFormKnoppen();
	}

	public function getFormId() {
		return $this->formId;
	}

	public function getDataTableId() {
		return $this->dataTableId;
	}

	/**
	 * Set the id late (after constructor).
	 * Use in case it is not POSTed.
	 *
	 * @param string $dataTableId
	 */
	public function setDataTableId($dataTableId) {
		$this->dataTableId = $dataTableId;
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
			$namespace = "CsrDelft\\view\\formulier\\";
			if (!isset($definition[1]) OR $definition[1] === false) {
				$class = 'Required';
			} else {
				$class = '';
			}
			$desc = ucfirst(str_replace('_', ' ', $fieldName));
			switch ($definition[0]) {
				case T::String:
					if (startsWith($fieldName, 'rechten_')) {
						$namespace .= 'invoervelden';
						$class .= 'RechtenField';
						break;
					}
				// fall through
				case T::Char:
					if ($fieldName === 'verticale') {
						$namespace .= 'keuzevelden';
						$class .= 'VerticaleField';
						break;
					}
					$namespace .= 'invoervelden';
					$class .= 'TextField';
					break;
				case T::Boolean:
					$namespace .= 'keuzevelden';
					$class .= 'JaNeeField';
					break;
				case T::Integer:
					$namespace .= 'getalvelden';
					$class .= 'IntField';
					break;
				case T::Float:
					$namespace .= 'getalvelden';
					$class .= 'FloatField';
					break;
				case T::Date:
					$namespace .= 'keuzevelden';
					$class .= 'DateField';
					break;
				case T::Time:
					$namespace .= 'keuzevelden';
					$class .= 'TimeField';
					break;
				case T::DateTime:
					$namespace .= 'keuzevelden';
					$class .= 'DateTimeField';
					break;
				case T::Text:
				case T::LongText:
					$namespace .= 'invoervelden';
					$class .= 'TextareaField';
					break;
				case T::Enumeration:
					$namespace .= 'keuzevelden';
					$class .= 'SelectField';
					break;
				case T::UID:
					$namespace .= 'invoervelden';
					$class .= 'LidField';
					break;
			}
			$namespacedClass = $namespace . '\\' . $class;
			if ($definition[0] == T::Enumeration) {
				$options = array();
				foreach ($definition[2]::getTypeOptions() as $option) {
					$options[$option] = $definition[2]::getDescription($option);
				}
				$fields[$fieldName] = new $namespacedClass($fieldName, $this->model->$fieldName, $desc, $options);
			} elseif ($definition[0] == T::Char) {
				$fields[$fieldName] = new $namespacedClass($fieldName, $this->model->$fieldName, $desc, 1);
			} else {
				$fields[$fieldName] = new $namespacedClass($fieldName, $this->model->$fieldName, $desc);
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
	 * @return InputField|false if not found
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

	public function removeField(FormElement $field) {
		$pos = array_search($field, $this->fields);
		unset($this->fields[$pos]);
	}

	public function getFormKnoppen() {
		return $this->formKnoppen;
	}

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted() {
		foreach ($this->fields as $field) {
			if ($field instanceof InputField AND !$field->isPosted()) {
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
			if ($field instanceof Validator AND !$field->validate()) { // geen comments bijv.
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

	protected function getJavascript() {
		foreach ($this->fields as $field) {
			$this->javascript .= $field->getJavascript();
		}
		if ($this->stappen_submit) {
			$this->javascript .= <<<JS

$(form).formSteps({submitButton: "{$this->stappen_submit}"});
JS;
		}
		return $this->javascript;
	}

	protected function getFormTag() {
		if ($this->dataTableId) {
			$this->css_classes[] = 'DataTableResponse';
		}
		return '<form enctype="' . $this->enctype . '" action="' . $this->action . '" id="' . $this->formId . '" data-tableid="' . $this->dataTableId . '" class="' . implode(' ', $this->css_classes) . '" method="' . ($this->post ? 'post' : 'get') . '">';
	}

	protected function getCsrfTag() {
	    return sprintf('<input type="hidden" name="_token" value="%s" />', CSRF_TOKEN);
    }

	protected function getScriptTag() {
		return <<<HTML
<script type="text/javascript">
$(document).ready(function () {
	var form = document.getElementById('{$this->formId}');
	{$this->getJavascript()}
});
</script>
HTML;
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 *
	 * @param boolean $showMelding Toon meldingen bovenaan formulier (set through property)
	 * @return void
	 */
	public function view() {
		if ($this->showMelding) {
			echo getMelding();
		}
		echo $this->getFormTag();
		echo $this->getCsrfTag();
		$titel = $this->getTitel();
		if (!empty($titel)) {
			echo '<h1 class="Titel">' . $titel . '</h1>';
		}
		if (isset($this->error)) {
			echo '<span class="error">' . $this->error . '</span>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->fields as $field) {
			$field->view();
		}
		echo $this->formKnoppen->getHtml();
		echo $this->getScriptTag();
		echo '</form>';
	}

	/**
	 * Geef een array terug van de gewijzigde velden.
	 *
	 * @returns ChangeLogEntry[]
	 */
	public function diff() {
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

    public function __toString() {
        ob_start();
        $this->view();
        return ob_get_clean();
    }
}
