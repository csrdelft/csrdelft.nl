<?php

namespace CsrDelft\view\cms;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\CmsPagina;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use CsrDelft\common\Util\DateUtil;
use Twig\Environment;

/**
 * CmsPaginaView.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bekijken van een CmsPagina.
 */
class CmsPaginaView implements View, ToResponse
{
	use ToHtmlResponse;
	private $pagina;

	public function __construct(CmsPagina $pagina)
	{
		$this->pagina = $pagina;
	}

	public function getModel()
	{
		return $this->pagina;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getTitel()
	{
		return $this->pagina->titel;
	}

	public function __toString()
	{
		return ContainerFacade::getContainer()
			->get(Environment::class)
			->render('cms/pagina-inhoud.html.twig', [
				'pagina' => $this->pagina,
			]);
	}
}
