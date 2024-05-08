<?php

namespace CsrDelft\common\Serializer\Normalizer;

use ArrayObject;
use CsrDelft\common\Enum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @package CsrDelft\common
 */
class EnumNormalizer implements NormalizerInterface
{
	/**
	 * @param Enum $enum
	 * @param string|null $format
	 * @param array $context
	 * @return array|ArrayObject|bool|float|int|string|null
	 */
	public function normalize($enum, string $format = null, array $context = [])
	{
		return $enum->getDescription();
	}

	public function supportsNormalization(
		$data,
		string $format = null,
		array $context = []
	) {
		return $data instanceof Enum;
	}
}
