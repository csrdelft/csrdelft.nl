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
	private $model;
	private $cols;
	private $attibuteName;
	public $required = false;
	/**
	 * @var string
	 */
	private $orm;

	/**
	 * TableField constructor.
	 * @param $attibuteName
	 * @param PersistentEntity[] $model
	 * @param string $orm
	 */
	public function __construct($attibuteName, $model, $orm) {
		/** @var PersistentEntity $ormInstance */
		$ormInstance = new $orm;

		foreach ($ormInstance->getAttributes() as $attribute) {
			if (in_array($attribute, $ormInstance->getPrimaryKey())) continue;

			$this->addCol($attribute, $ormInstance->getAttributeDefinition($attribute));
		}
		$this->attibuteName = $attibuteName;
		$this->orm = $orm;

		if ($this->isPosted()) {
			$this->model = $this->getValue();
		} else {
			$this->model = $model;
		}
	}

	protected function addCol($attribute, $definition) {
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
		$head = '';

		foreach ($this->cols as $name => $definition) {
			$required = !isset($definition[1]) || !$definition[1];
			$head .= '<th>' . preg_replace('/_/', ' ', ucfirst($name)) . ($required ? '<span class="field-required">*</span>' : '') . '</th>';
		}

		$body = '';
		foreach ($this->model as $i => $entity) {
			$body .= '<tr>';
			foreach ($this->cols as $name => $definition) {
				$body .= '<td>' . $this->getTag($definition, $this->attibuteName . '[' . $i . '][' . $name . ']', $entity->$name) . '</td>';
			}

			$body .= '<td><a class="btn btn-sm" href="#"><span class="ico cross"></span></a></td>';
			$body .= '</tr>';
		}

		return <<<HTML
<table class="table">
<thead>
<tr>
${head}
<th></th>
</tr>
</thead>
<tbody>
${body}
<tr>
<td></td>
<td>Totaal</td>
<td><input type="text" readonly class="form-control form-control-sm" value="€5,00"></td>
<td></td>
<td><a class="btn btn-sm btn-outline-primary" href="#"><span class="ico add"></span></a></td>
</tr>
</tbody>
</table>
HTML;

	}

	private function getTag($definition, $name, $value) {
		switch ($definition[0]) {
			case T::String:
			case T::StringKey:
			case T::Date:
				return sprintf('<input type="text" class="form-control form-control-sm" value="%s" name="%s" />', $value, $name);
			case T::Enumeration:
				/** @var PersistentEnum $enum */
				$enum = $definition[2];
				$typeOptions = $enum::getTypeOptions();
				$return = '<input type="hidden" value="" name="' . $name . '" />';
				foreach ($typeOptions as $option) {
					$checked = $value == $option ? 'checked="checked" ' : '';
					$return .= <<<HTML
<div class="custom-control custom-radio custom-control-inline">
<input type="radio" class="custom-control-input" id="{$name}-{$option}" value="{$option}" name="{$name}" {$checked}/>
<label class="custom-control-label" for="{$name}-{$option}">{$enum::getDescription($option)}</label>
</div>
HTML;
				}
				return $return;
			default:
				return 'not implemented';
		}
	}

	public function getTitel() {
		return '';
	}

	public function getBreadcrumbs() {
		return '';
	}

	public function validate() {
		$input = filter_input(INPUT_POST, $this->attibuteName, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
		foreach ($input as $entity) {
			foreach ($this->cols as $attribute => $definition) {
				if ($entity[$attribute] == '' && (!isset($definition[1]) || !$definition[1])) return false;
			}
		}

		return true;
	}

	public function getError() {
		// TODO: Implement getError() method.
	}

	public function isPosted() {
		$input = filter_input(INPUT_POST, $this->attibuteName, FILTER_DEFAULT, FILTER_FORCE_ARRAY);

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
		$input = filter_input(INPUT_POST, $this->attibuteName, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
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

	public function getName() {
		return $this->attibuteName;
	}
}
