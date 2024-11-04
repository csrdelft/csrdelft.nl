<?php

namespace CsrDelft\common\Util;

final class SqlUtil
{
	public static function sql_contains(string|null $field): string
	{
		return "%$field%";
	}
}
