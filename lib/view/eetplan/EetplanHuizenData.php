<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\Component\DataTable\CustomDataTableEntry;

class EetplanHuizenData implements CustomDataTableEntry
{
	/**
	 * @return string[]
	 *
	 * @psalm-return list{'id'}
	 */
	public static function getIdentifierFieldNames()
	{
		return ['id'];
	}

	public static function getFieldNames()
	{
		return ['id', 'naam', 'soort', 'eetplan'];
	}
}
