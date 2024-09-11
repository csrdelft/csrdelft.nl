<?php

namespace CsrDelft\view\datatable;

/**
 * Cell formatting maakt het mogelijk bepaalde data te negeren met filteren en sorteren.
 *
 * @see https://datatables.net/reference/option/columns.type
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 08/03/2018
 */
class CellType
{
	const STRING = 'string';
	const FORMATTED_NUMBER = 'num-fmt';

	public function __construct(protected $choice)
	{
	}

	public function getChoice()
	{
		return $this->choice;
	}

	public static function String()
	{
		return new static(self::STRING);
	}

	public static function FormattedNumber()
	{
		return new static(self::FORMATTED_NUMBER);
	}
}
