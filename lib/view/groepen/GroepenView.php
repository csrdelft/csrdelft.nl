<?php

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Enum;
use CsrDelft\common\Security\Voter\Entity\Groep\AbstractGroepVoter;
use CsrDelft\entity\groepen\enum\GroepTab;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\repository\GroepRepository;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\View;
use Twig\Environment;

class GroepenView implements View
{
	use ToHtmlResponse;

	private $model;
	/**
	 * @var Groep[]
	 */
	private $groepen;
	/**
	 * @var Enum|null
	 */
	private $soort;
	private $geschiedenis;
	private $tab;
	private $pagina;
	private $paginaNummer;
	/**
	 * @var int
	 */
	private $paginaGrootte;
	/**
	 * @var int
	 */
	private $totaal;
	/**
	 * @var callable|null
	 */
	private $urlGetter;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(
		Environment $twig,
		GroepRepository $model,
		$groepen,
		$soort = null,
		$paginaNummer = 0,
		$paginaGrootte = 0,
		$totaal = 0,
		callable $urlGetter = null,
		$geschiedenis = false
	) {
		$this->model = $model;
		$this->groepen = $groepen;
		$this->soort = $soort;
		$this->geschiedenis = $geschiedenis;
		if ($model instanceof BesturenRepository) {
			$this->tab = GroepTab::Lijst;
		} else {
			$this->tab = GroepTab::Pasfotos;
		}
		$cmsPaginaRepository = ContainerFacade::getContainer()->get(
			CmsPaginaRepository::class
		);
		$this->pagina = $cmsPaginaRepository->find(
			'groepsbeschrijving_' . $model->getNaam()
		);
		if (!$this->pagina) {
			$this->pagina = $cmsPaginaRepository->find('');
		}
		$this->paginaNummer = $paginaNummer;
		$this->paginaGrootte = $paginaGrootte;
		$this->totaal = $totaal;
		$this->urlGetter = $urlGetter;
		$this->twig = $twig;
	}

	public function getBreadcrumbs()
	{
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/">' .
			Icon::getTag('home') .
			'</a></li>' .
			'<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>' .
			'<li class="breadcrumb-item active">' .
			$this->getTitel() .
			'</li></ul>';
	}

	public function getModel()
	{
		return $this->groepen;
	}

	public function getTitel()
	{
		return $this->pagina->titel;
	}

	public function __toString()
	{
		$orm = $this->model->getEntityClassName();
		$html = '';
		if ($orm::magAlgemeen(AccessAction::Aanmaken(), $this->soort)) {
			$html .=
				'<a class="btn" href="' .
				$this->model->getUrl() .
				'/nieuw/' .
				($this->soort ? $this->soort->getValue() : '') .
				'">' .
				Icon::getTag('toevoegen') .
				' Toevoegen</a>';
		}
		$html .=
			'<a class="btn" href="' .
			$this->model->getUrl() .
			'/beheren">' .
			Icon::getTag('table') .
			' Beheren</a>';
		if ($this->geschiedenis) {
			$html .=
				'<a id="deelnamegrafiek" class="btn post" href="' .
				$this->model->getUrl() .
				'/' .
				$this->geschiedenis .
				'/deelnamegrafiek">' .
				Icon::getTag('chart-line') .
				' Deelnamegrafiek</a>';
		}
		$view = new CmsPaginaView($this->pagina);
		$html .= $view->__toString();
		$security = ContainerFacade::getContainer()->get('security');
		foreach ($this->groepen as $groep) {
			// Controleer rechten
			if (!$security->isGranted(AbstractGroepVoter::BEKIJKEN, $groep)) {
				continue;
			}
			$html .= '<hr>';
			$view = new GroepView(
				$this->twig,
				$groep,
				$this->tab,
				$this->geschiedenis
			);
			$html .= $view->__toString();
		}

		// Alleen pagination laten zien als nodig.
		if ($this->totaal != $this->paginaGrootte) {
			$html .= $this->getPagination();
		}

		return $html;
	}

	private function url($paginaNummer)
	{
		$getter = $this->urlGetter;
		return $getter($paginaNummer);
	}

	/**
	 * @param string $html
	 * @return string
	 */
	private function getPagination(): string
	{
		$html = '';
		if ($this->paginaNummer == 1) {
			$vorigeDisabledClass = ' disabled';
			$vorigeLink = '';
		} else {
			$vorigeDisabledClass = '';
			$vorigeLink = $this->url($this->paginaNummer - 1);
		}
		if ($this->paginaNummer == ceil($this->totaal / $this->paginaGrootte)) {
			$volgendeDisabledClass = ' disabled';
			$volgendeLink = '';
		} else {
			$volgendeDisabledClass = '';
			$volgendeLink = $this->url($this->paginaNummer + 1);
		}

		$html .= <<<HTML
<nav aria-label="Page navigation example">
  <ul class="pagination">
    <li class="page-item{$vorigeDisabledClass}"><a class="page-link" href="{$vorigeLink}">Vorige</a></li>
HTML;
		for ($i = 1; $i <= ceil($this->totaal / $this->paginaGrootte); $i++) {
			$activeClass = $this->paginaNummer == $i ? ' active' : '';
			$html .= <<<HTML
    <li class="page-item{$activeClass}"><a class="page-link" href="{$this->url(
				$i
			)}">{$i}</a></li>
HTML;
		}

		$html .= <<<HTML
    <li class="page-item{$volgendeDisabledClass}"><a class="page-link" href="{$volgendeLink}">Volgende</a></li>
  </ul>
</nav>
HTML;
		return $html;
	}
}
