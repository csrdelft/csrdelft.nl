<?php

namespace CsrDelft\view\datatable;

/**
 * Render functies voor dataTables.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 08/03/2018
 *
 * @see assets/js/datatable/render.js
 */
class CellRender
{
	const DEFAULT = 'default';
	const CHECK = 'check';
	const BEDRAG = 'bedrag';
	const AANMELD_FILTER = 'aanmeldFilter';
	const AANMELDINGEN = 'aanmeldingen';
	const TOTAAL_PRIJS = 'totaalPrijs';
	const TIMEAGO = 'timeago';
	const FILESIZE = 'filesize';
	const DATETIME = 'datetime';
	const DATE = 'date';
	const TIME = 'time';

	/** @var string */
	protected $choice;

	/**
	 * @param string $choice
	 */
	public function __construct(string $choice)
	{
		$this->choice = $choice;
	}

	/**
	 * @return string
	 */
	public function getChoice()
	{
		return $this->choice;
	}

	/**
	 * @return static
	 */
	public static function Default(): static
	{
		return new static(self::DEFAULT);
	}

	/**
	 * @return static
	 */
	public static function Check(): static
	{
		return new static(self::CHECK);
	}

	/**
	 * @return static
	 */
	public static function Bedrag(): static
	{
		return new static(self::BEDRAG);
	}

	/**
	 * @return static
	 */
	public static function AanmeldFilter(): static
	{
		return new static(self::AANMELD_FILTER);
	}

	/**
	 * @return static
	 */
	public static function Aanmeldingen(): static
	{
		return new static(self::AANMELDINGEN);
	}

	/**
	 * @return static
	 */
	public static function TotaalPrijs(): static
	{
		return new static(self::TOTAAL_PRIJS);
	}

	/**
	 * @return static
	 */
	public static function Timeago(): static
	{
		return new static(self::TIMEAGO);
	}

	/**
	 * @return static
	 */
	public static function Filesize(): static
	{
		return new static(self::FILESIZE);
	}

	public static function DateTime(): static
	{
		return new static(self::DATETIME);
	}

	public static function Date(): static
	{
		return new static(self::DATE);
	}
	public static function Time(): static
	{
		return new static(self::TIME);
	}
}
