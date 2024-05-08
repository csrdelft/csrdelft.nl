<?php

namespace CsrDelft\Component\Formulier;

use Closure;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class FormulierBuilder
{
	public $post = true;
	public $showMelding = true;
	public $preventCsrf = true;
	public $css_classes = [];
	public $titel;
	protected $model;
	protected $formId;
	protected $dataTableId;
	protected $action = null;
	protected $error;
	protected $enctype = 'multipart/form-data';
	protected $formKnoppen;
	protected $javascript = '';
	/**
	 * Fields must be added via addFields()
	 * or insertElementBefore() methods,
	 * and retrieved with getFields() method.
	 *
	 * @var FormElement[]
	 */
	private $fields = [];
	/**
	 * @var array
	 */
	private $validationMethods = [];
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(RequestStack $requestStack, Environment $twig)
	{
		$this->css_classes[] = 'Formulier';
		$this->formKnoppen = new FormDefaultKnoppen();
		$this->requestStack = $requestStack;
		$this->twig = $twig;
	}

	public function setShowMelding($showMelding): void
	{
		$this->showMelding = $showMelding;
	}

	public function setFormId($formId): void
	{
		$this->formId = $formId;
	}

	public function getFormId()
	{
		return $this->formId;
	}

	public function getDataTableId()
	{
		return $this->dataTableId;
	}

	/**
	 * Set the id late (after constructor).
	 * Use in case it is not POSTed.
	 *
	 * @param string|bool $dataTableId
	 */
	public function setDataTableId($dataTableId): void
	{
		// Link with DataTable?
		if ($dataTableId === true) {
			$this->dataTableId = $this->requestStack
				->getCurrentRequest()
				->request->filter('DataTableId', '', FILTER_SANITIZE_STRING);
		} else {
			$this->dataTableId = $dataTableId;
		}
	}

	public function getTitel()
	{
		return $this->titel;
	}

	/**
	 * @param false|mixed $titel
	 */
	public function setTitel($titel): void
	{
		$this->titel = $titel;
	}

	public function getModel()
	{
		return $this->model;
	}

	public function getBreadcrumbs(): null
	{
		return null;
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function hasFields(): bool
	{
		return !empty($this->fields);
	}

	/**
	 * Zoekt een InputField met exact de gegeven naam.
	 *
	 * @param string $fieldName
	 * @return InputField|false if not found
	 */
	public function findByName($fieldName): InputField|FileField|bool
	{
		foreach ($this->fields as $field) {
			if (
				($field instanceof InputField || $field instanceof FileField) &&
				$field->getName() === $fieldName
			) {
				return $field;
			}
		}
		return false;
	}

	public function addFields(array $fields): void
	{
		$this->fields = array_merge($this->fields, $fields);
	}

	public function insertAtPos($pos, FormElement $field): void
	{
		array_splice($this->fields, $pos, 0, [$field]);
	}

	public function removeField(FormElement $field): void
	{
		$pos = array_search($field, $this->fields);
		unset($this->fields[$pos]);
	}

	public function getFormulier(): FormulierInstance
	{
		return new FormulierInstance(
			$this->twig,
			$this->action,
			$this->titel,
			$this->dataTableId,
			$this->formKnoppen,
			$this->fields,
			$this->showMelding,
			$this->preventCsrf,
			$this->css_classes,
			$this->validationMethods,
			$this->post
		);
	}

	/**
	 * @param null $action
	 */
	public function setAction($action): void
	{
		$this->action = $action;
	}

	public function addCssClass($class): void
	{
		$this->css_classes[] = $class;
	}

	/**
	 * @param FormKnoppen $formKnoppen
	 */
	public function setFormKnoppen(FormKnoppen $formKnoppen): void
	{
		$this->formKnoppen = $formKnoppen;
	}

	/**
	 * @param \Closure $param Kan alle velden controleren als er false wordt gereturned is ($fields: FormElement[]) => boolean
	 */
	public function addValidationMethod(\Closure $param): void
	{
		$this->validationMethods[] = $param;
	}

	/**
	 * @param mixed $model
	 */
	public function setModel($model): void
	{
		$this->model = $model;
	}
}
