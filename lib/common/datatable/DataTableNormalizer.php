<?php


namespace CsrDelft\common\datatable;


use CsrDelft\common\datatable\annotation\DataTable;
use CsrDelft\common\datatable\annotation\DataTableColumn;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use function common\short_class;

/**
 * @package CsrDelft\common
 */
class DataTableNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface {
	use NormalizerAwareTrait;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	private function getProperty($entity, $field) {
		if (method_exists($entity, $field)) {
			return call_user_func([$entity, $field]);
		} elseif(property_exists($entity, $field)) {
			return $entity->$field;
		}

		throw new InvalidArgumentException("Object " . get_class($entity) . " heeft geen property " . $field);
	}

	public function normalize($topic, string $format = null, array $context = []) {
		$metadata = $this->entityManager->getClassMetadata(get_class($topic));

		$annotationReader = new DataTableAnnotationReader(get_class($topic));
		$properties = $annotationReader->getProperties();

		foreach ($properties as $field => $property) {
			if ($property instanceof DataTableColumn) {

				$data[$property->name] = $this->normalizeIfNeeded($this->getProperty($topic, $field), $format, $context);
			} elseif ($property instanceof Id) {
				$data[$field] = $this->normalizeIfNeeded($this->getProperty($topic, $field), $format, $context);
			}
		}

		$data['UUID'] = strtolower(sprintf('%s@%s.csrdelft.nl', implode('.', $metadata->getIdentifierValues($topic)), short_class($topic)));

		return $data;
	}

	private function normalizeIfNeeded($value, $format, $context) {
		if (is_object($value)) {
			return $this->normalizer->normalize($value, $format, $context);
		} else {
			return $value;
		}
	}

	public function supportsNormalization($data, string $format = null, array $context = []) {
		if (!is_object($data)) {
			return false;
		}

		try {
			$reflectionClass = new \ReflectionClass($data);
		} catch (\Exception $ex) {
			return false;
		}
		$reader = new AnnotationReader();

		return $reader->getClassAnnotation($reflectionClass, DataTable::class) != null;
	}
}
