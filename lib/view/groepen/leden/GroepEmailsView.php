<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use Twig\Environment;

class GroepEmailsView implements ToResponse, \Stringable
{
	use ToHtmlResponse;

	public function __construct(private Environment $twig, private Groep $groep)
	{
	}

	public function __toString(): string
	{
		return $this->twig->render('groep/emails.html.twig', [
			'groep' => $this->groep,
		]);
	}
}
