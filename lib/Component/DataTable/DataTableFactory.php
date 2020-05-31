<?php


namespace CsrDelft\Component\DataTable;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class DataTableFactory {
	/** @var ContainerInterface */
	private $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	private function getType($type) {
		if ($this->registry->has($type)) {
			return $this->registry->get($type);
		} else {
			return new $type();
		}
	}

	/**
	 * @param $entityType
	 * @param $dataUrl
	 * @return DataTableBuilder
	 */
	public function create($entityType, $dataUrl) {
		return $this->createWithType(DefaultDataTableType::class, $entityType, $dataUrl);
	}

	/**
	 * @param $type
	 * @param $entityType
	 * @param $dataUrl
	 * @return DataTableBuilder
	 */
	public function createWithType($type, $entityType = null, $dataUrl = null) {
		/** @var DataTableTypeInterface $type */
		$type = $this->registry->get($type);

		$builder = new DataTableBuilder();

		$type->createDataTable($builder, [
			AbstractDataTableType::OPTION_ENTITY_TYPE => $entityType,
			AbstractDataTableType::OPTION_DATA_URL => $dataUrl,
		]);

		return $builder;
	}
}
