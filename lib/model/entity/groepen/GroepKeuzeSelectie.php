<?php

namespace CsrDelft\model\entity\groepen;

use Stringable;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuzeSelectie implements Stringable
{
	/**
	 * @param string|null $naam
	 * @param string|null $selectie
	 */
	public function __construct(
		#[Serializer\Groups('vue')] public $naam = null,
		#[Serializer\Groups('vue')] public $selectie = null
	) {
	}

	public function __toString(): string
	{
		return "$this->naam: $this->selectie";
	}
}
