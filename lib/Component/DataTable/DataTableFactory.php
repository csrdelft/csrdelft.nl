<?php

namespace CsrDelft\Component\DataTable;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DataTableFactory
{
	/**
	 * @param ContainerInterface $registry
	 */
	public function __construct(
		private $registry,
		private readonly DataTableBuilder $builder
	) {
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
