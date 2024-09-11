<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\entity\groepen\enum\GroepKeuzeType;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuze
{
	public function __wakeup()
	{
		assert(in_array($this->type, GroepKeuzeType::getEnumValues()));
	} // Checks, radios, dropdown, text, slider, number, date
	/**
	 * @var string[]
	 */
	#[Serializer\Groups('vue')]
	public $opties;

	/**
	 * @param string $naam
	 * @param string $type
	 * @param string $default
	 * @param string $description
	 */
	public function __construct(
		#[Serializer\Groups('vue')] public $naam = null,
		#[Serializer\Groups('vue')] public $type = null,
		#[Serializer\Groups('vue')] public $default = null,
		#[Serializer\Groups('vue')] public $description = null
	) {
	}
}
