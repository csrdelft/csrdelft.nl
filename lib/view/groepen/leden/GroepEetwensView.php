<?php

namespace CsrDelft\view\groepen\leden;

use Stringable;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use Twig\Environment;

class GroepEetwensView implements ToResponse, Stringable
{
	use ToHtmlResponse;

	public function __construct(private Environment $twig, private Groep $groep)
	{
	}

	public function __toString(): string
	{
		return $this->twig->render('groep/eetwens.html.twig', [
			'groep' => $this->groep,
		]);
	}
}
