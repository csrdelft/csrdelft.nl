<?php
/**
 * GroepOmschrijvingView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use Twig\Environment;

class GroepOmschrijvingView implements ToResponse, \Stringable
{
	use ToHtmlResponse;

	protected $groep;
	/**
	 * @var Environment
	 */
	protected $twig;

	public function __construct(Environment $twig, Groep $groep)
	{
		$this->groep = $groep;
		$this->twig = $twig;
	}

	public function __toString(): string
	{
		return $this->twig->render('groep/omschrijving.html.twig', [
			'groep' => $this->groep,
		]);
	}
}
