<?php

namespace CsrDelft\view\formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\service\CsrfService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\EmptyFormKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\Validator;
use CsrDelft\view\View;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Alle dingen die we in de field-array van een Formulier stoppen
 * moeten een uitbreiding zijn van FormElement.
 *
 * @see FormElement
 */
class Formulier implements View, Validator, ToResponse {
	use ToHtmlResponse;
	protected $model;
	protected $formId;
	protected $dataTableId;
	protected $action = null;
	public $post = true;
	protected $error;
	protected $enctype = 'multipart/form-data';
	public $showMelding = true;
	public $preventCsrf = true;
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

	public function __construct($model, $action, $titel = false, $dataTableId = false) {
		$this->model = $model;
		$this->formId = uniqid_safe(classNameZonderNamespace(get_class($this->model == null ? $this : $this->model)));
		$this->action = $action;
		$this->titel = $titel;
		$this->css_classes[] = 'Formulier';
		// Link with DataTable?
		if ($dataTableId === true) {
			$this->dataTableId = ContainerFacade::getContainer()->get('request_stack')
				->getCurrentRequest()
				->request->filter('DataTableId', '', FILTER_SANITIZE_STRING);
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

	/**
	 * @param InputField|TextField $field
	 */
	private function loadProperty($field) {
		$fieldName = $field->getName();
		if ($this->model) {
			if (method_exists($this->model, 'set' . ucfirst($fieldName))) {
				call_user_func([$this->model, 'set' . ucfirst($fieldName)], $field->getFormattedValue());
			} elseif (property_exists($this->model, $fieldName)) {
				$this->model->$fieldName = $field->getFormattedValue();
			}
		}
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
	 * @return InputField|TextField|false if not found
	 */
	public function findByName($fieldName) {
		foreach ($this->fields as $field) {
			if (($field instanceof InputField OR $field instanceof FileField || $field instanceof TextField) AND $field->getName() === $fieldName) {
				return $field;
			}
		}
		return false;
	}

	public function addFields(array $fields) {
		foreach ($fields as $field) {
			if ($field instanceof InputField || $field instanceof TextField) {
				$this->loadProperty($field);
			}
		}
		$this->fields = array_merge($this->fields, $fields);
	}

	public function insertAtPos($pos, FormElement $field) {
		if ($field instanceof InputField || $field instanceof TextField) {
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
			if (($field instanceof InputField || $field instanceof TextField) AND !$field->isPosted()) {
				//setMelding($field->getName() . ' is niet gepost', 2); //DEBUG
				return false;
			}
		}
		return $_SERVER['REQUEST_METHOD'] == 'POST';
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
			if ($field instanceof InputField || $field instanceof TextField) {
				$values[$field->getName()] = $field->getFormattedValue();
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
		return $this->javascript;
	}

	protected function getFormTag() {
		if ($this->dataTableId) {
			$this->css_classes[] = 'DataTableResponse';
		}
		return '<form enctype="' . $this->enctype . '" action="' . htmlspecialchars ($this->action) . '" id="' . $this->formId . '" data-tableid="' . $this->dataTableId . '" class="' . implode(' ', $this->css_classes) . '" method="' . ($this->post ? 'post' : 'get') . '">';
	}

	protected function getScriptTag() {
		return <<<HTML
<script type="text/javascript">
docReady(function() {
	var form = document.getElementById('{$this->formId}');
	{$this->getJavascript()}
});
</script>
HTML;
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 *
	 * @return string
	 */
	public function __toString() {
		$string = '';

		if ($this->showMelding) {
			$string .= getMelding();
		}
		$string .= $this->getFormTag();
		$titel = $this->getTitel();
		if (!empty($titel)) {
			$string .= '<h1 class="Titel">' . $titel . '</h1>';
		}
		if (isset($this->error)) {
			$string .= '<span class="error">' . $this->error . '</span>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->fields as $field) {
			$string .= $field->__toString();
		}
		$csrfField = $this->getCsrfField();
		if ($csrfField != null)
			$string .= $csrfField->__toString();
		$string .= $this->formKnoppen->getHtml();
		$string .= $this->getScriptTag();
		$string .= '</form>';

		return $string;
	}

	public function getCsrfField() {
		if (!$this->preventCsrf) {
			return null;
		}
		$csrfService = ContainerFacade::getContainer()->get(CsrfService::class);
		$token = $csrfService->generateToken($this->action, $this->getMethod());
		return new CsrfField($token);
	}

	/**
	 * Geef een array terug van de gewijzigde velden.
	 *
	 * @returns ChangeLogEntry[]
	 */
	public function diff() {
		$changeLogRepository = ContainerFacade::getContainer()->get(ChangeLogRepository::class);
		$diff = array();
		foreach ($this->getFields() as $field) {
			if ($field instanceof InputField || $field instanceof TextField) {
				$old = $field->getOrigValue();
				$new = $field->getValue();
				if ($old !== $new) {
					$prop = $field->getName();
					$diff[$prop] = $changeLogRepository->nieuw($this->getModel(), $prop, $old, $new);
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
			$changelog .= '[div]Bewerking van [lid=' . LoginService::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][br]';
			foreach ($diff as $change) {
				$changelog .= '(' . $change->property . ') ' . $change->old_value . ' => ' . $change->new_value . '[br]';
			}
			$changelog .= '[/div][hr]';
		}
		return $changelog;
	}

	public function getMethod() {
		return $this->post ? 'post' : 'get';
	}
}
