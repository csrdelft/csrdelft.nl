<?php

namespace CsrDelft\common\Doctrine\Type\Serializer;

use Zumba\JsonSerializer\Exception\JsonSerializerException;

class SafeJsonSerializerException extends JsonSerializerException
{
	/**
	 * SafeJsonSerializerException constructor.
	 * @param string $string
	 */
	public function __construct($string)
	{
		parent::__construct($string);
	}
}
