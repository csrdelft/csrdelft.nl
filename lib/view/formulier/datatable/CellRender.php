<?php

namespace CsrDelft\view\formulier\datatable;

/**
 * Render functies voor dataTables.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 08/03/2018
 *
 * @see resources/assets/js/datatable.js
 */
class CellRender {
	const NONE = 'null';
	const DEFAULT = '$.fn.dataTable.render.default';
	const CHECK = '$.fn.dataTable.render.check';
	const BEDRAG = '$.fn.dataTable.render.bedrag';
	const AANMELD_FILTER = '$.fn.dataTable.render.aanmeldFilter';
	const AANMELDINGEN = '$.fn.dataTable.render.aanmeldingen';
	const TOTAAL_PRIJS = '$.fn.dataTable.render.totaalPrijs';

	/** @var string */
	protected $choice;

	/**
	 * @param string $choice
	 */
	public function __construct(string $choice) {
		$this->choice = $choice;
	}

	/**
	 * @return string
	 */
	public function getChoice() {
		return $this->choice;
	}

	/**
	 * @return static
	 */
	public static function None() {
		return new static(self::NONE);
	}

	/**
	 * @return static
	 */
	public static function Default() {
		return new static(self::DEFAULT);
	}

	/**
	 * @return static
	 */
	public static function Check() {
		return new static(self::CHECK);
	}

	/**
	 * @return static
	 */
	public static function Bedrag() {
		return new static(self::BEDRAG);
	}

	/**
	 * @return static
	 */
	public static function AanmeldFilter() {
		return new static(self::AANMELD_FILTER);
	}

	/**
	 * @return static
	 */
	public static function Aanmeldingen() {
		return new static(self::AANMELDINGEN);
	}

	/**
	 * @return static
	 */
	public static function TotaalPrijs() {
		return new static(self::TOTAAL_PRIJS);
	}
}
