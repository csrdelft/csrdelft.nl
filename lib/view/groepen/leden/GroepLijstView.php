<?php
/**
 * GroepLijstView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class GroepLijstView implements ToResponse
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
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$lid = $em
			->getRepository(GroepLid::class)
			->nieuw($this->groep, LoginService::getUid());

		return $this->twig->render('groep/lijst.html.twig', [
			'groep' => $this->groep,
			'aanmeldForm' => new GroepAanmeldenForm($lid, $this->groep, false),
		]);
	}
}
