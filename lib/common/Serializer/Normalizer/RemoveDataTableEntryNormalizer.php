<?php


namespace CsrDelft\common\Serializer\Normalizer;


use ArrayObject;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Als er een object genormalizeerd wordt met interface DataTableEntry, voeg dan het veld UUID toe.
 *
 * @package CsrDelft\common
 */
class RemoveDataTableEntryNormalizer implements ContextAwareNormalizerInterface
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var ObjectNormalizer
	 */
	private $normalizer;

	public function __construct(EntityManagerInterface $entityManager, ObjectNormalizer $normalizer)
	{
		$this->entityManager = $entityManager;
		$this->normalizer = $normalizer;
	}

	/**
	 * @param RemoveDataTableEntry $removed
	 * @param string|null $format
	 * @param array $context
	 * @return array|ArrayObject|bool|float|int|string|null
	 */
	public function normalize($removed, string $format = null, array $context = [])
	{
		$id = $removed->getId();

		if (!is_array($id)) {
			$id = [$id];
		}
		return [
			'UUID' => strtolower(sprintf('%s@%s.csrdelft.nl', implode('.', $id), short_class($removed->getClass()))),
			'remove' => true,
		];
	}

	public function supportsNormalization($data, string $format = null, array $context = [])
	{
		return $data instanceof RemoveDataTableEntry;
	}
}
