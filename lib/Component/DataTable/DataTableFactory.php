<?php


namespace CsrDelft\Component\DataTable;


use Symfony\Component\DependencyInjection\ContainerInterface;

class DataTableFactory
{
	/** @var ContainerInterface */
	private $registry;
	/**
	 * @var DataTableBuilder
	 */
	private $builder;

	public function __construct($registry, DataTableBuilder $builder)
	{
		$this->registry = $registry;
		$this->builder = $builder;
	}

	/**
	 * @param $type
	 * @return DataTableTypeInterface|object
	 */
	private function getType($type)
	{
		if ($this->registry->has($type)) {
			return $this->registry->get($type);
		} else {
			return new $type();
		}
	}

	/**
	 * @param $type
	 * @param $options
	 * @return DataTableBuilder
	 */
	public function create($type, $options)
	{
		$type = $this->getType($type);

		$type->createDataTable($this->builder, $options);

		return $this->builder;
	}
}
