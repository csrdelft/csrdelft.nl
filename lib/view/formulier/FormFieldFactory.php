<?php

namespace CsrDelft\view\formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\Doctrine\Type\DateTimeImmutableType;
use CsrDelft\common\Doctrine\Type\Enum\EnumType;
use CsrDelft\common\Doctrine\Type\LongTextType;
use CsrDelft\common\Doctrine\Type\SafeJsonType;
use CsrDelft\common\Doctrine\Type\StringKeyType;
use CsrDelft\common\Doctrine\Type\UidType;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\view\formulier\getalvelden\FloatField;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\ProfielEntityField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\SafeJsonField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use CsrDelft\view\formulier\keuzevelden\EnumSelectField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\TimeObjectField;
use CsrDelft\view\formulier\keuzevelden\VerticaleField;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Exception;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/09/2018
 */
class FormFieldFactory {
	/**
	 * @param $model
	 * @return InputField[]
	 * @throws Exception
	 */
	public static function generateFields($model) {
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		/** @var ClassMetadata $meta */
		$meta = $em->getClassMetadata(get_class($model));

		$fields = array();
		foreach ($meta->getFieldNames() as $fieldName) {
			$type = Type::getTypeRegistry()->get($meta->getTypeOfField($fieldName));
			$field = static::getFieldByType($fieldName, $model->$fieldName, $type);

			if (!$meta->isNullable($fieldName)) {
				$field->required = true;
			}

			if ($meta->isIdentifier($fieldName)) {
				$field->readonly = true;
				$field->hidden = true;
				$field->required = false;
			}

			$fields[$fieldName] = $field;
		}

		foreach ($meta->getAssociationMappings() as $associationMapping) {
			// We supporten alleen als de key in dit entity zit.
			if (!$associationMapping['isOwningSide']) {
				continue;
			}

			$fieldName = $associationMapping['fieldName'];

			if (count($associationMapping['joinColumns']) !== 1) {
				throw new CsrException('Compound joinColumns worden niet ondersteund voor veld ' . $fieldName . ' in class ' . get_class($model));
			}

			unset($fields[$fieldName]);

			$targetEntity = $associationMapping['targetEntity'];

			$readableFieldName = humanizeVariable($fieldName);

			if ($targetEntity == Profiel::class) {
				$field = new ProfielEntityField($fieldName, $model->$fieldName, $readableFieldName, 'leden');
			} else {
				$field = new DoctrineEntityField($fieldName, $model->$fieldName, $readableFieldName, $targetEntity, '');
			}

			$joinColumn = $associationMapping['joinColumns'][0];

			if (isset($joinColumn['nullable']) && !$joinColumn['nullable']) {
				$field->required = true;
			}

			if ($meta->isIdentifier($joinColumn['name'])) {
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
	 * @param Type $type
	 * @return FloatField|IntField|LidField|RechtenField|SafeJsonField|TextareaField|TextField|DateTimeField|EnumSelectField|JaNeeField|TimeObjectField|VerticaleField
	 */
	private static function getFieldByType(string $fieldName, $value, $type) {
		$desc = humanizeVariable($fieldName);

		if (str_starts_with($fieldName, 'rechten_')) {
			return new RechtenField($fieldName, $value, $desc);
		}

		if ($fieldName === 'verticale') {
			return new VerticaleField($fieldName, $value, $desc);
		}

		if ($type instanceof EnumType) {
			return new EnumSelectField($fieldName, $value, $desc, $type->getEnumClass());
		}

		if ($type instanceof IntegerType) {
			return new IntField($fieldName, $value, $desc, 0);
		}

		if ($type instanceof StringKeyType || $type instanceof StringType) {
			return new TextField($fieldName, $value, $desc);
		}

		if ($type instanceof DateTimeImmutableType) {
			return new DateTimeField($fieldName, $value, $desc);
		}

		if ($type instanceof TextType || $type instanceof LongTextType) {
			return new TextareaField($fieldName, $value, $desc);
		}

		if ($type instanceof SafeJsonType) {
			return new SafeJsonField($fieldName, $value, $desc);
		}

		if ($type instanceof UidType) {
			return new LidField($fieldName, $value, $desc);
		}

		if ($type instanceof BooleanType) {
			return new JaNeeField($fieldName, $value, $desc);
		}

		if ($type instanceof FloatType) {
			return new FloatField($fieldName, $value, $desc, null);
		}

		if ($type instanceof TimeImmutableType) {
			return new TimeObjectField($fieldName, $value, $desc);
		}

		throw new CsrException("Kan geef formulier genereren voor veld $fieldName van type $type.");
	}
}
