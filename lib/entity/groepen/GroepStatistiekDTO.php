<?php

namespace CsrDelft\entity\groepen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/06/2019
 */
class GroepStatistiekDTO
{
	public function __construct(
		public $totaal,
		public $verticale,
		public $geslacht,
		public $lichting,
		public $tijd
	) {
	}
}
