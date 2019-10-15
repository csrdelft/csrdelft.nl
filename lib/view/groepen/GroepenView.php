<?php

namespace CsrDelft\view\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\GroepTab;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\groepen;
use CsrDelft\view\groepen\GroepView;
use CsrDelft\view\Icon;
use CsrDelft\view\View;

class GroepenView implements View {

	private $model;
	/**
	 * @var AbstractGroep[]
	 */
	private $groepen;
	private $soort;
	private $geschiedenis;
	private $tab;
	private $pagina;

	public function __construct(
		AbstractGroepenModel $model,
		$groepen,
		$soort = null,
		$geschiedenis = false
	) {
		$this->model = $model;
		$this->groepen = $groepen;
		$this->soort = $soort;
		$this->geschiedenis = $geschiedenis;
		if ($model instanceof BesturenModel) {
			$this->tab = GroepTab::Lijst;
		} else {
			$this->tab = GroepTab::Pasfotos;
		}
		$this->pagina = CmsPaginaModel::get($model->getNaam());
		if (!$this->pagina) {
			$this->pagina = CmsPaginaModel::get('');
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
		$orm = $model::ORM;
		if ($orm::magAlgemeen(AccessAction::Aanmaken, null, $this->soort)) {
			echo '<a class="btn" href="' . $this->model->getUrl() . 'nieuw/' . $this->soort . '">' . Icon::getTag('add') . ' Toevoegen</a>';
		}
		echo '<a class="btn" href="' . $this->model->getUrl() . 'beheren">' . Icon::getTag('table') . ' Beheren</a>';
		if ($this->geschiedenis) {
			echo '<a id="deelnamegrafiek" class="btn post" href="' . $this->model->getUrl() . $this->geschiedenis . '/deelnamegrafiek">' . Icon::getTag('chart_bar') . ' Deelnamegrafiek</a>';
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
