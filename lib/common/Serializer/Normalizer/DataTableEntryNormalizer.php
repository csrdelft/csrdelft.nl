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
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var ObjectNormalizer
	 */
	private $normalizer;

	public function __construct(
		EntityManagerInterface $entityManager,
		ObjectNormalizer $normalizer
	) {
		$this->entityManager = $entityManager;
		$this->normalizer = $normalizer;
	}

	public function normalize($topic, string $format = null, array $context = []): string|int|float|bool|\ArrayObject|array|null
	{
		$metadata = $this->entityManager->getClassMetadata(get_class($topic));

		$data = $this->normalizer->normalize($topic, $format, $context);

		$data['UUID'] = strtolower(
			sprintf(
				'%s@%s.csrdelft.nl',
				implode('.', $metadata->getIdentifierValues($topic)),
				ReflectionUtil::short_class($topic)
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
