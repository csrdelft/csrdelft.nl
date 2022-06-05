<?php

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Enum;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\enum\GroepTab;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\View;

class GroepenView implements View {
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

	public function __construct(
        GroepRepository $model,
        $groepen,
        $soort = null,
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
		$cmsPaginaRepository = ContainerFacade::getContainer()->get(CmsPaginaRepository::class);
		$this->pagina = $cmsPaginaRepository->find('groepsbeschrijving_' . $model->getNaam());
		if (!$this->pagina) {
			$this->pagina = $cmsPaginaRepository->find('');
		}
	}

	public function getBreadcrumbs() {
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/">' . Icon::getTag('home') . '</a></li>'
			. '<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>'
			. '<li class="breadcrumb-item active">' . $this->getTitel() . '</li></ul>';
	}

	public function getModel() {
		return $this->groepen;
	}

	public function getTitel() {
		return $this->pagina->titel;
	}

	public function __toString() {
		$model = $this->model;
		$orm = $model->entityClass;
		$html = '';
		if ($orm::magAlgemeen(AccessAction::Aanmaken(), null, $this->soort)) {
			$html .= '<a class="btn btn-light" href="' . $this->model->getUrl() . '/nieuw/' . ($this->soort ? $this->soort->getValue() : '') . '">' . Icon::getTag('add') . ' Toevoegen</a>';
		}
		$html .= '<a class="btn btn-light" href="' . $this->model->getUrl() . '/beheren">' . Icon::getTag('table') . ' Beheren</a>';
		if ($this->geschiedenis) {
			$html .= '<a id="deelnamegrafiek" class="btn btn-light post" href="' . $this->model->getUrl() . "/" . $this->geschiedenis . '/deelnamegrafiek">' . Icon::getTag('chart_bar') . ' Deelnamegrafiek</a>';
		}
		$view = new CmsPaginaView($this->pagina);
		$html .= $view->__toString();
		foreach ($this->groepen as $groep) {
			// Controleer rechten
			if (!$groep->mag(AccessAction::Bekijken())) {
				continue;
			}
			$html .= '<hr>';
			$view = new GroepView($groep, $this->tab, $this->geschiedenis);
			$html .= $view->__toString();
		}

		return $html;
	}

}
