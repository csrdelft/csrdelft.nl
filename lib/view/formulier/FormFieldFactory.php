<?php

namespace CsrDelft\view\formulier;

use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\PersistentEnum;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\formulier\getalvelden\FloatField;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\DateField;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\keuzevelden\TimeField;
use CsrDelft\view\formulier\keuzevelden\VerticaleField;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/09/2018
 */
class FormFieldFactory {
	/**
	 * @param PersistentEntity $model
	 * @return InputField[]
	 * @throws \Exception
	 */
	public static function generateFields(PersistentEntity $model) {
		$fields = array();
		foreach ($model->getAttributes() as $fieldName) {
			$definition = $model->getAttributeDefinition($fieldName);

			$additional = isset($definition[2]) ? $definition[2] : null;
			$field = static::getFieldByType($fieldName, $model->$fieldName, $definition[0], $additional);

			if (!isset($definition[1]) || $definition[1] === false) {
				$field->required = true;
			}

			if (in_array($fieldName, $model->getPrimaryKey())) {
				$field->readonly = true;
				$field->hidden = true;
				$field->required = false;
			}

			$fields[$fieldName] = $field;
		}

		return $fields;
	}

	/**
	 * @param string $fieldName
	 * @param mixed $value
	 * @param string $type
	 * @param string|null $additional
	 * @return InputField
	 * @throws \Exception
	 */
	private static function getFieldByType(string $fieldName, $value, string $type, string $additional = null) {
		$desc = ucfirst(str_replace('_', ' ', $fieldName));

		switch ($type) {
			case T::String:
			case T::StringKey:
				if (startsWith($fieldName, 'rechten_')) {
					return new RechtenField($fieldName, $value, $desc);
				}

				return new TextField($fieldName, $value, $desc);
			case T::Char:
				if ($fieldName === 'verticale') {
					return new VerticaleField($fieldName, $value, $desc);
				}

				return new TextField($fieldName, $value, $desc, 1);
			case T::Boolean:
				return new JaNeeField($fieldName, $value, $desc);
			case T::Integer:
				return new IntField($fieldName, $value, $desc, 0);
			case T::Float:
				return new FloatField($fieldName, $value, $desc, null);
			case T::Date:
				return new DateField($fieldName, $value, $desc);
			case T::Time:
				return new TimeField($fieldName, $value, $desc);
			case T::DateTime:
				return new DateTimeField($fieldName, $value, $desc);
			case T::Text:
			case T::LongText:
				return new TextareaField($fieldName, $value, $desc);
			case T::Enumeration:
				$options = array();
				/** @var PersistentEnum $additional */
				foreach ($additional::getTypeOptions() as $option) {
					$options[$option] = $additional::getDescription($option);
				}
				return new SelectField($fieldName, $value, $desc, $options);
			case T::UID:
				return new LidField($fieldName, $value, $desc);
			default:
				throw new CsrException("Kan geef formulier genereren voor veld $fieldName van type $type.");
		}
	}
}
