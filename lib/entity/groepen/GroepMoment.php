<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\enum\GroepStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepMoment
{
	/**
	 * Datum en tijd begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $beginMoment;
	/**
	 * Datum en tijd einde
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $eindMoment;
}
