<?php

namespace CsrDelft\view\formulier\invoervelden;

class SafeJsonField extends TextareaField
{
	public function __construct(
		$name,
		$value,
		$description,
		$rows = 2,
		$max_len = null,
		$min_len = null
	) {
		parent::__construct(
			$name,
			json_encode($value, JSON_PRETTY_PRINT),
			$description,
			$rows,
			$max_len,
			$min_len
		);
	}
	public function getFormattedValue(): mixed
	{
		return json_decode(
			htmlspecialchars_decode(parent::getFormattedValue()),
			true
		);
	}
}
