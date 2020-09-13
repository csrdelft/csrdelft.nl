<?php

namespace CsrDelft\view\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Enum;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\enum\GroepTab;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\AbstractGroepenRepository;
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
	 * @var AbstractGroep[]
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
		AbstractGroepenRepository $model,
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
		return '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>'
			. '<li class="breadcrumb-item active">' . $this->getTitel() . '</li></ul>';
	}

	public function getModel() {
		return $this->groepen;
	}

	public function getTitel() {
		return $this->pagina->titel;
	}

	public function view() {
		$model = $this->model;
		$orm = $model->entityClass;
		if ($orm::magAlgemeen(AccessAction::Aanmaken, null, $this->soort)) {
			echo '<a class="btn" href="' . $this->model->getUrl() . '/nieuw/' . $this->soort . '">' . Icon::getTag('add') . ' Toevoegen</a>';
		}
		echo '<a class="btn" href="' . $this->model->getUrl() . '/beheren">' . Icon::getTag('table') . ' Beheren</a>';
		if ($this->geschiedenis) {
			echo '<a id="deelnamegrafiek" class="btn post" href="' . $this->model->getUrl() . "/" . $this->geschiedenis . '/deelnamegrafiek">' . Icon::getTag('chart_bar') . ' Deelnamegrafiek</a>';
		}
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->groepen as $groep) {
			// Controleer rechten
			if (!$groep->mag(AccessAction::Bekijken)) {
				continue;
			}
			echo '<hr>';
			$view = new GroepView($groep, $this->tab, $this->geschiedenis);
			$view->view();
		}
	}

}
