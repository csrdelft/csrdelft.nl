<?php

namespace CsrDelft\entity\forum;

use DateTime;

/**
 * Data klasse om een zoekopdracht op het forum bij te houden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/03/2019
 */
class ForumZoeken
{
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
	 * @var string
	 */
	public $van;
	/**
	 * @var string
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

	public function __construct()
	{
		$this->zoek_in = ['titel', 'alle_berichten', 'eerste_bericht'];
		$this->van = (new DateTime())->modify('-1 year')->format('Y-m-d');
		$this->tot = (new DateTime())->modify('+1 day')->format('Y-m-d');
		$this->sorteer_op = 'laatste_bericht';
		$this->sorteer_volgorde = 'desc';
		$this->limit = 20;
	}

	public static function nieuw($zoekterm, $limit, $zoek_in): static
	{
		$forumZoeken = new static();
		$forumZoeken->zoekterm = $zoekterm;
		$forumZoeken->limit = $limit;
		$forumZoeken->zoek_in = $zoek_in;

		return $forumZoeken;
	}
}
