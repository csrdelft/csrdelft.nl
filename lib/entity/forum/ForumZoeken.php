<?php

namespace CsrDelft\entity\forum;

use DateTime;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/03/2019
 */
class ForumZoeken {
	public $zoekterm;
	public $sorteer_volgorde;
	public $sorteer_op;
	public $van;
	public $tot;
	public $zoek_in;
	public $limit;

	public function __construct() {
		$this->zoek_in = ['titel', 'alle_berichten', 'eerste_bericht'];
		$this->van = (new DateTime())->modify('-1 year')->format('Y-m-d');
		$this->tot = (new DateTime())->modify('+1 day')->format('Y-m-d');
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
