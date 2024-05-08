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
	public function __wakeup(): void
	{
		assert(in_array($this->type, GroepKeuzeType::getEnumValues()));
	}

	/**
	 * @var string
	 * @Serializer\Groups("vue")
	 */
	public $naam;
	/**
	 * @var string
	 * @Serializer\Groups("vue")
	 */
	public $type; // Checks, radios, dropdown, text, slider, number, date
	/**
	 * @var string[]
	 * @Serializer\Groups("vue")
	 */
	public $opties; // String, names, name
	/**
	 * @var string
	 * @Serializer\Groups("vue")
	 */
	public $default; // String, names, name
	/**
	 * @var string
	 * @Serializer\Groups("vue")
	 */
	public $description;

	public function __construct(
		$naam = null,
		$type = null,
		$default = null,
		$description = null
	) {
		$this->naam = $naam;
		$this->type = $type;
		$this->default = $default;
		$this->description = $description;
	}
}
