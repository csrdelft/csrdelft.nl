<?php

namespace CsrDelft\entity\forum;

use DateTime;

/**
 * Data klasse om een zoekopdracht op het forum bij te houden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/03/2019
 */
class ForumZoeken {
	/**
	 * @var string
	 */
	public $zoekterm;
	/**
	 * @var string
	 */
	public $sorteer_volgorde;
	/**
	 * @var string
	 */
	public $sorteer_op;
	/**
	 * @var \DateTimeInterface
	 */
	public $van;
	/**
	 * @var \DateTimeInterface
	 */
	public $tot;
	/**
	 * @var array
	 */
	public $zoek_in;
	/**
	 * @var int
	 */
	public $limit;

	public function __construct() {
		$this->zoek_in = ['titel', 'alle_berichten', 'eerste_bericht'];
		$this->van = date_create_immutable('-1 year');
		$this->tot = date_create_immutable('+1 day');
		$this->sorteer_op = 'laatste_bericht';
		$this->sorteer_volgorde = 'desc';
		$this->limit = 20;
	}

	public static function nieuw($zoekterm, $limit, $zoek_in) {
		$forumZoeken = new static();
		$forumZoeken->zoekterm = $zoekterm;
		$forumZoeken->limit = $limit;
		$forumZoeken->zoek_in = $zoek_in;

		return $forumZoeken;
	}
}
