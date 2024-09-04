<?php

namespace CsrDelft\common\Serializer\Normalizer;

use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Als er een object genormalizeerd wordt met interface DataTableEntry, voeg dan het veld UUID toe.
 *
 * @package CsrDelft\common
 */
class DataTableEntryNormalizer implements ContextAwareNormalizerInterface
{
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly ObjectNormalizer $normalizer
	) {
	}

	public function normalize($topic, string $format = null, array $context = [])
	{
		$metadata = $this->entityManager->getClassMetadata($topic::class);

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
	) {
		return $data instanceof DataTableEntry;
	}
}
