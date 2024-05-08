<?php

namespace CsrDelft\common\Doctrine\Type\Serializer;

use ReflectionClass;
use Zumba\JsonSerializer\JsonSerializer;

/**
 * JsonSerializer that only allows serializing and deserializing of classes that are explicitly allowed.
 * @author Sander
 * @since 13-07-2018
 */
class SafeJsonSerializer extends JsonSerializer
{
	/**
	 * Array of allowed classes
	 * @var string[]
	 */
	protected $allowedClasses;

	/**
	 * SafeJsonSerializer constructor.
	 * @param string[] $allowedClasses The classnames of classes that this serializer is allowed to (de)serialize. Passing null will allow all classes.
	 * @param array $customObjectSerializerMap
	 */
	public function __construct(
		array $allowedClasses = null,
		array $customObjectSerializerMap = []
	) {
		parent::__construct(null, $customObjectSerializerMap);
		$this->allowedClasses = (array) $allowedClasses;
	}

	/**
	 * @param object $value
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function serializeObject($value): array
	{
		$ref = new ReflectionClass($value);
		$className = $ref->getName();
		if ($this->classAllowed($className)) {
			return parent::serializeObject($value);
		} else {
			throw new SafeJsonSerializerException(
				"Serializing of $className is not allowed by this SafeJsonSerializer"
			);
		}
	}

	protected function unserializeObject($value)
	{
		$className = $value[static::CLASS_IDENTIFIER_KEY];
		if ($this->classAllowed($className)) {
			return parent::unserializeObject($value);
		} else {
			throw new SafeJsonSerializerException(
				"Deserializing of $className is not allowed by this SafeJsonSerializer"
			);
		}
	}

	/**
	 * Whether this classname is allowed to be (un)serialized.
	 * @param $className
	 */
	protected function classAllowed($className): bool
	{
		return $this->allowedClasses === null ||
			in_array($className, $this->allowedClasses);
	}
}
