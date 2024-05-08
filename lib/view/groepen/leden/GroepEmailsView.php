<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use Twig\Environment;

class GroepEmailsView implements ToResponse
{
	use ToHtmlResponse;

	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var Groep
	 */
	private $groep;

	public function __construct(Environment $twig, Groep $groep)
	{
		$this->twig = $twig;
		$this->groep = $groep;
	}

	public function __toString()
	{
		return $this->twig->render('groep/emails.html.twig', [
			'groep' => $this->groep,
		]);
	}
}
