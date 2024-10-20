<?php
/**
 * GroepPasfotosView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use Stringable;
use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use Twig\Environment;

class GroepPasfotosView implements ToResponse, Stringable
{
	use ToHtmlResponse;

	public function __construct(private Environment $twig, private $groep)
	{
	}

	public function __toString(): string
	{
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$lid = $em
			->getRepository(GroepLid::class)
			->nieuw($this->groep, LoginService::getUid());
		$form = new GroepAanmeldenForm($lid, $this->groep);
		$form->css_classes[] = 'pasfotos';

		return $this->twig->render('groep/pasfotos.html.twig', [
			'groep' => $this->groep,
			'aanmeldForm' => $form,
		]);
	}
}
