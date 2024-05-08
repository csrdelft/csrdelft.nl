<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\Component\DataTable\CustomDataTableEntry;

class EetplanHuizenData implements CustomDataTableEntry
{
	public static function getIdentifierFieldNames(): array
	{
		return ['id'];
	}

	public static function getFieldNames(): array
	{
		return ['id', 'naam', 'soort', 'eetplan'];
	}
}
