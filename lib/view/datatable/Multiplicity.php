<?php

namespace CsrDelft\view\datatable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 08/03/2018
 *
 * @see assets/js/util.js
 */
class Multiplicity {
	const NONE = '';
	const ZERO = '== 0';
	const ONE = '== 1';
	const TWO = '== 2';
	const ANY = '>= 1';

	protected $choice;

	public function __construct($choice) {
		$this->choice = $choice;
	}

	public function getChoice() {
		return $this->choice;
	}

	/**
	 * Niet gelimiteerd.
	 *
	 * @return static
	 */
	public static function None() {
		return new static(self::NONE);
	}

	/**
	 * Alleen nul.
	 *
	 * @return static
	 */
	public static function Zero() {
		return new static(self::ZERO);
	}

	/**
	 * Alleen één.
	 *
	 * @return static
	 */
	public static function One() {
		return new static(self::ONE);
	}

	/**
	 * Alleen twee.
	 *
	 * @return static
	 */
	public static function Two() {
		return new static(self::TWO);
	}

	/**
	 * Ten minste één.
	 *
	 * @return static
	 */
	public static function Any() {
		return new static(self::ANY);
	}
}
