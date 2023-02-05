<?php

namespace CsrDelft\view\cms;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\entity\CmsPagina;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use CsrDelft\common\Util\DateUtil;

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
		$security = ContainerFacade::getContainer()->get('security');
		$html = '';
		$html .= MeldingUtil::getMelding();
		if ($security->isGranted(CmsPaginaVoter::BEWERKEN, $this->pagina)) {
			$html .=
				'<a href="/pagina/bewerken/' .
				$this->pagina->naam .
				'" class="btn float-end" title="Bewerk pagina&#013;' .
				$this->pagina->laatstGewijzigd->format(DateUtil::DATETIME_FORMAT) .
				'">' .
				Icon::getTag('bewerken') .
				'</a>';
		}
		$html .= CsrBB::parseHtml(
			htmlspecialchars_decode($this->pagina->inhoud),
			$this->pagina->inlineHtml
		);
		return $html;
	}
}
