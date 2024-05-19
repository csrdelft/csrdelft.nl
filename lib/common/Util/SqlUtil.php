<?php

namespace CsrDelft\common\Util;

final class SqlUtil
{
	public static function sql_contains($field): string
	{
		return "%$field%";
	}
}
