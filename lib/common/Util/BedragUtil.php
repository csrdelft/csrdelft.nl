<?php

namespace CsrDelft\common\Util;

final class BedragUtil
{
	/**
	 * @param int $bedrag Bedrag in euros
	 * @return string Geformat met euro, bij hele euro's met ",-"
	 */
	public static function format_euro($bedrag)
	{
		$bedragtekst = sprintf('%.2f', $bedrag);
		$leesbaar = str_replace(',00', ',-', $bedragtekst);
		return '€ ' . $leesbaar;
	}

	/**
	 * @param int $bedrag Bedrag in centen
	 * @return string Geformat zonder euro
	 */
	public static function format_bedrag_kaal($bedrag)
	{
		return sprintf('%.2f', $bedrag / 100);
	}

	/**
	 * @param int $bedrag Bedrag in centen
	 * @return string Geformat met euro
	 */
	public static function format_bedrag($bedrag)
	{
		return '€' . static::format_bedrag_kaal($bedrag);
	}
}
