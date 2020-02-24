<?php


namespace CsrDelft\common\datatable;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use function common\short_class;

/**
 * Als er een object genormalizeerd wordt met interface DataTableEntry, voeg dan het veld UUID toe.
 *
 * @package CsrDelft\common
 */
class RemoveDataTableEntryNormalizer implements ContextAwareNormalizerInterface {
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var ObjectNormalizer
	 */
	private $normalizer;

	public function __construct(EntityManagerInterface $entityManager, ObjectNormalizer $normalizer) {
		$this->entityManager = $entityManager;
		$this->normalizer = $normalizer;
	}

	public function normalize($removed, string $format = null, array $context = []) {
		$topic = $removed->getEntity();

		$metadata = $this->entityManager->getClassMetadata(get_class($topic));

		return [
			'UUID' => strtolower(sprintf('%s@%s.csrdelft.nl', implode('.', $metadata->getIdentifierValues($topic)), short_class($topic))),
			'remove' => true,
		];
	}

	public function supportsNormalization($data, string $format = null, array $context = []) {
		return $data instanceof RemoveDataTableEntry;
	}
}
