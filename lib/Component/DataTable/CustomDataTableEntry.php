<?php

namespace CsrDelft\Component\DataTable;

/**
 * Implementeer deze interface om een datatable te maken voor een object die niet in Doctrine zit.
 *
 * @package CsrDelft\common\datatable
 */
interface CustomDataTableEntry
{
	/**
	 * Velden die de primary key van deze tabel zijn.
	 *
	 * @return array
	 */
	public static function getIdentifierFieldNames();

	/**
	 * Standaard veldnamen voor deze tabel.
	 *
	 * @return mixed
	 */
	public static function getFieldNames();
}
