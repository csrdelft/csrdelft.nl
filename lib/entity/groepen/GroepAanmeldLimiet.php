<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepAanmeldLimiet
{
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer', nullable: true)]
	public $aanmeldLimiet;
}
