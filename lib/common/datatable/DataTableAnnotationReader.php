<?php


namespace CsrDelft\common\datatable;


use CsrDelft\common\CsrException;
use CsrDelft\common\datatable\annotation\DataTableColumn;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class DataTableAnnotationReader {
	/**
	 * @var Reader
	 */
	private $reader;
	/**
	 * @var NameConverterInterface
	 */
	private $converter;
	/**
	 * @var \ReflectionClass
	 */
	private $reflectionClass;
	/**
	 * @var annotation\DataTable
	 */
	private $dataTable;
	/**
	 * @var annotation\DataTableKnop
	 */
	private $knoppen;

	public function __construct($class) {
		$this->reader = new AnnotationReader();
		$this->converter = new CamelCaseToSnakeCaseNameConverter();
		$this->reflectionClass = new \ReflectionClass($class);

		$this->getClassAttributes();
	}

	/**
	 * @param $class
	 * @return DataTableColumn[]
	 */
	public function getProperties() {
		$reflectionProperties = $this->reflectionClass->getProperties();
		$reflectionMethods = $this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

		$properties =[];

		foreach ($reflectionProperties as $property) {
			/** @var \CsrDelft\common\datatable\annotation\DataTableColumn|null $propertyAnnotation */
			if ($propertyAnnotation = $this->reader->getPropertyAnnotation($property, \CsrDelft\common\datatable\annotation\DataTableColumn::class)) {
				if (!$propertyAnnotation->name) {
					$propertyAnnotation->name = $property->name;
				}
				$properties[$property->name] = $propertyAnnotation;
			} elseif ($idAnnotation = $this->reader->getPropertyAnnotation($property, Id::class)) {
				$idAnnotation->name = $property->name;
				$properties[$property->name] = $idAnnotation;
			}
		}

		$converter = new CamelCaseToSnakeCaseNameConverter();

		foreach ($reflectionMethods as $method) {
			if ($method->getDeclaringClass()->name !== $this->reflectionClass->name) {
				continue;
			}

			/** @var \CsrDelft\common\datatable\annotation\DataTableColumn|null $methodAnnotation */
			if ($methodAnnotation = $this->reader->getMethodAnnotation($method, \CsrDelft\common\datatable\annotation\DataTableColumn::class)) {
				if (!$methodAnnotation->name) {
					$accessor = preg_match('/^(get)(.+)$/i', $method->name, $matches);
					if ($accessor) {
						$attributeName = lcfirst($matches[2]);

						$methodAnnotation->name = $converter->normalize($attributeName);
					} else {
						throw new CsrException("Methode " . $method->name . " in " . $this->reflectionClass->class . " begint niet met 'get' en heeft wel het DataTableColumn attribuut.");
					}
				}

				$properties[$method->name] = $methodAnnotation;
			}
		}

		return $properties;
	}

	public function getClassAttributes() {
		$classAnnotation = $this->reader->getClassAnnotations($this->reflectionClass);
		$this->knoppen = [];
		$order = null;

		foreach ($classAnnotation as $annotation) {
			if ($annotation instanceof \CsrDelft\common\datatable\annotation\DataTableKnop) {
				$this->knoppen[] = $annotation->getKnop();
			}

			if ($annotation instanceof \CsrDelft\common\datatable\annotation\DataTable) {
				$this->dataTable = $annotation;
			}
		}
	}

	public function getKnoppen() {
		return $this->knoppen;
	}

	public function getConfig() {
		return $this->dataTable;
	}
}
