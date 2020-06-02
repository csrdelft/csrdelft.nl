<?php


namespace CsrDelft\Component\DataTable;


use Symfony\Component\DependencyInjection\ContainerInterface;

class DataTableFactory {
	/** @var ContainerInterface */
	private $registry;
	/**
	 * @var DataTableBuilder
	 */
	private $builder;

	public function __construct($registry, DataTableBuilder $builder) {
		$this->registry = $registry;
		$this->builder = $builder;
	}

	/**
	 * @param $type
	 * @return DataTableTypeInterface|object
	 */
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
		return $this->createWithType(DefaultDataTableType::class, [
			AbstractDataTableType::OPTION_ENTITY_TYPE => $entityType,
			AbstractDataTableType::OPTION_DATA_URL => $dataUrl
		]);
	}

	/**
	 * @param $type
	 * @param $options
	 * @return DataTableBuilder
	 */
	public function createWithType($type, $options) {
		$type = $this->getType($type);

		$type->createDataTable($this->builder, $options);

		return $this->builder;
	}
}
