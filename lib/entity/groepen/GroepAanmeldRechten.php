<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftAanmeldRechten;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Implementeerd @see HeeftAanmeldRechten
 */
trait GroepAanmeldRechten
{
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	#[Serializer\Groups('datatable')]
	public $rechtenAanmelden;

	public function getAanmeldRechten()
	{
		return $this->rechtenAanmelden;
	}

	public function setAanmeldRechten($rechten)
	{
		$this->rechtenAanmelden = $rechten;
	}
}
