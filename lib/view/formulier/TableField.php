<?php

namespace CsrDelft\view\formulier;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\Validator;

/**
 * Voor modellen met een 1-n relatie.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class TableField implements FormElement, Validator, PostedValue {
	/**
	 * @var bool
	 */
	public $required = false;
	/**
	 * @var PersistentEntity[]
	 */
	private $model;
	/**
	 * @var array
	 */
	private $primary_key;
	/**
	 * @var array
	 */
	private $cols;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $orm;
	/**
	 * @var array
	 */
	private $error;
	/**
	 * @var null
	 */
	private $sum;

	/**
	 * TableField constructor.
	 * @param $name
	 * @param PersistentEntity[] $model
	 * @param string $orm
	 * @param null $sum
	 */
	public function __construct($name, $model, $orm, $sum = null) {
		$this->name = $name;
		$this->orm = $orm;
		$this->sum = $sum;

		$this->error = [];
		$this->primary_key = [];

		/** @var PersistentEntity $ormInstance */
		$ormInstance = new $orm;
		foreach ($ormInstance->getAttributes() as $attribute) {
			if (in_array($attribute, $ormInstance->getPrimaryKey())) {
				$this->primary_key[] = $attribute;
			} else {
				$this->addCol($attribute, $ormInstance->getAttributeDefinition($attribute));
			}
		}

		if ($this->isPosted()) {
			$this->model = $this->getValue();
		} else {
			$this->model = $model;
		}
	}

	protected function addCol($attribute, $definition) {
		$this->cols[$attribute] = $definition;
	}

	public function isPosted() {
		$input = filter_input(INPUT_POST, $this->name, FILTER_DEFAULT, FILTER_FORCE_ARRAY);

		if (count($input) == 0) {
			return $this->required;
		}

		foreach ($input as $entity) {
			// Als een entity gepost is moet ie in z'n geheel gepost worden.
			foreach ($this->cols as $attribute => $definition) {
				if (!isset($entity[$attribute])) return false;
			}
		}

		return true;
	}

	public function getValue() {
		$input = filter_input(INPUT_POST, $this->name, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
		$values = [];

		foreach ($input as $entity) {
			$value = new $this->orm;
			foreach ($this->cols as $attribute => $definition) {
				$value->$attribute = $entity[$attribute];
			}

			$values[] = $value;
		}

		return $values;
	}

	public function changeCol($attribute, $definition) {
		$this->cols[$attribute] = $definition;
	}

	public function getType() {
	}

	public function getJavascript() {
		return '';
	}

	public function getModel() {
		return $this->model;
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getHtml() {
		$head = $this->createHead();
		$body = $this->createBody();
		$footer = $this->createFooter();

		return <<<HTML
<table class="table">
<thead>${head}</thead>
<tbody>${body}${footer}</tbody>
</table>
HTML;
	}

	private function createHead() {
		$head = '<tr>';

		foreach ($this->cols as $name => $definition) {
			$required = !isset($definition[1]) || !$definition[1];
			$head .= '<th>' . preg_replace('/_/', ' ', ucfirst($name)) . ($required ? '<span class="field-required">*</span>' : '') . '</th>';
		}

		$head .= '<th></th>';
		$head .= '</tr>';

		return $head;
	}

	private function createBody() {
		$body = '';
		foreach ($this->model as $i => $entity) {
			$body .= '<tr>';

			foreach ($this->primary_key as $key) {
				// Keys mogen niet blind naar de db geschreven worden.
				$body .= sprintf('<input type="hidden" name="%s" value="%s" />', $this->createName($this->name, $i, $key), $entity->$key);
			}

			foreach ($this->cols as $name => $definition) {
				$body .= '<td>' . $this->getTag($definition, $this->createName($this->name, $i, $name), $entity->$name) . '</td>';
			}

			$body .= '<td><a class="btn btn-sm" href="#"><span class="ico cross"></span></a></td>';
			$body .= '</tr>';
		}

		return $body;
	}

	private function createName($name, $i, $attribute) {
		return sprintf('%s[%s][%s]', $name, $i, $attribute);
	}

	private function getTag($definition, $name, $value) {
		$error = isset($this->error[$name]) ? $this->error[$name] : '';
		$return = '';

		$classList = [];
		if ($error != '') {
			$classList[] = 'is-invalid';
		}

		switch ($definition[0]) {
			case 'bedrag':
				$return .= sprintf('<input type="text" class="form-control form-control-sm %s" value="%s" name="%s" data-inputmask-alias="bedrag" />', implode(' ', $classList), $value, $name);
				break;
			case T::String:
			case T::StringKey:
			case T::Date:
				$return .= sprintf('<input type="text" class="form-control form-control-sm %s" value="%s" name="%s" />', implode(' ', $classList), $value, $name);
				break;
			case T::Enumeration:
				/** @var PersistentEnum $enum */
				$enum = $definition[2];
				$typeOptions = $enum::getTypeOptions();
				$return .= '<input type="hidden" value="" name="' . $name . '" />';
				foreach ($typeOptions as $option) {
					$checked = $value == $option ? 'checked="checked" ' : '';
					/** @noinspection PhpUnhandledExceptionInspection */
					$return .= <<<HTML
<div class="custom-control custom-radio custom-control-inline">
<input type="radio" class="custom-control-input" id="{$name}-{$option}" value="{$option}" name="{$name}" {$checked}/>
<label class="custom-control-label" for="{$name}-{$option}">{$enum::getDescription($option)}</label>
</div>
HTML;
				}
				break;
			default:
				$return .= '<div class="invalid-feedback">not implemented</div>';
		}

		if ($error != '') {
			$return .= sprintf('<div class="invalid-feedback">%s</div>', $error);
		}

		return $return;
	}

	private function createFooter() {
		$return = '<tr>';
		if ($this->sum) {
			$loc = array_search($this->sum, array_keys($this->cols));

			for ($i = 0; $i < $loc - 1; $i++) {
				$return .= '<td></td>';
			}

			$return .= '<td>Totaal: </td>';
			$return .= sprintf('<td><input type="text" readonly class="form-control form-control-sm" data-sum="%s" data-inputmask-alias="bedrag"></td>', $this->name . '*' . $this->sum);

			for ($i = $loc + 1; $i < count(array_keys($this->cols)); $i++) {
				$return .= '<td></td>';
			}

		} else {
			for ($i = 0; $i < count(array_keys($this->cols)); $i++) {
				$return .= '<td></td>';
			}
		}

		$return .= '<td><a class="btn btn-sm" href="#"><span class="ico add"></span></a></td>';
		$return .= '</tr>';
		return $return;
	}

	public function getTitel() {
		return '';
	}

	public function getBreadcrumbs() {
		return '';
	}

	public function validate() {
		$input = filter_input(INPUT_POST, $this->name, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
		foreach ($input as $i => $entity) {
			foreach ($this->cols as $attribute => $definition) {
				if ($entity[$attribute] == '' && (!isset($definition[1]) || !$definition[1])) {
					$this->error[$this->createName($this->name, $i, $attribute)] = 'Dit is een verplicht veld';
				}
			}
		}

		return count($this->error) == 0;
	}

	public function getError() {
		return $this->error;
	}

	public function getName() {
		return $this->name;
	}
}
