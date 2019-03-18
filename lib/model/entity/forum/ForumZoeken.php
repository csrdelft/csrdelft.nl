<?php

namespace CsrDelft\model\entity\forum;

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
		$this->van = (new \DateTime())->modify('-1 year')->format('Y-m-d');
		$this->tot = (new \DateTime())->format('Y-m-d');
		$this->sorteer_op = 'laatste_bericht';
		$this->sorteer_volgorde = 'desc';
		$this->limit = 20;
	}
}
