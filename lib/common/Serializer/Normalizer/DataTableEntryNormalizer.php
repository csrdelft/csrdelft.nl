<?php

namespace CsrDelft\common\Serializer\Normalizer;

use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Als er een object genormalizeerd wordt met interface DataTableEntry, voeg dan het veld UUID toe.
 *
 * @package CsrDelft\common
 */
class DataTableEntryNormalizer implements NormalizerInterface
{
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly ObjectNormalizer $normalizer
	) {
	}

	/**
	* @inheritDoc
	* @return array|string|int|float|bool|\ArrayObject|null
	*/
	public function normalize($object, string $format = null, array $context = [])
	{
		$metadata = $this->entityManager->getClassMetadata($object::class);

		$data = $this->normalizer->normalize($object, $format, $context);

		$data['UUID'] = strtolower(
			sprintf(
				'%s@%s.csrdelft.nl',
				implode('.', $metadata->getIdentifierValues($object)),
				ReflectionUtil::short_class($object)
			)
		);

		return $data;
	}

	public function supportsNormalization(
		$data,
		string $format = null,
		array $context = []
	): bool {
		return $data instanceof DataTableEntry;
	}

	public function getSupportedTypes(?string $format): array
	{
		return $this->normalizer->getSupportedTypes($format);
	}
}
