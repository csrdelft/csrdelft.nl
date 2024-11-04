<?php

namespace CsrDelft\model\entity\groepen;

use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuzeSelectie implements \Stringable
{


	public function __toString(): string
	{
		return "$this->naam: $this->selectie";
	}
}
