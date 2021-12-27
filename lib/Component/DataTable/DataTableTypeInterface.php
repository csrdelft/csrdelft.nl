<?php


namespace CsrDelft\Component\DataTable;


/**
 * Interface DataTableTypeInterface
 * @package CsrDelft\Component\DataTable
 * @see Kernel hier wordt deze interface automatisch getagged met 'csr.table.type'
 */
interface DataTableTypeInterface {
	public function createDataTable(DataTableBuilder $builder, array $options): void;
}
