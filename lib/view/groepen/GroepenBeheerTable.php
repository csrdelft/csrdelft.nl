<?php

namespace CsrDelft\view\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;


/**
 * GroepenBeheerTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GroepenBeheerTable extends DataTable {

	private $naam;
	private $pagina;

	public function __construct(AbstractGroepenModel $model) {
		parent::__construct($model::ORM, $model->getUrl() . 'beheren', null);

		$this->selectEnabled = false;

		$this->naam = $model->getNaam();
		$this->titel = 'Beheer ' . $this->naam;

		$this->pagina = CmsPaginaModel::get($this->naam);
		if (!$this->pagina) {
			$this->pagina = CmsPaginaModel::get('');
		}

		$this->hideColumn('id', false);
		$this->hideColumn('samenvatting');
		$this->hideColumn('omschrijving');
		$this->hideColumn('maker_uid');
		$this->hideColumn('keuzelijst');
		$this->hideColumn('rechten_aanmelden');
		$this->hideColumn('status_historie');
		$this->searchColumn('naam');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$this->deleteColumn('keuzelijst2');

		$this->setOrder(['id' => 'desc']);

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'voorbeeld', 'Voorbeeldweergave van de ketzer', 'show'));

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $model->getUrl() . 'nieuw', 'Nieuw', 'Nieuwe toevoegen', 'toevoegen'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'aanmaken', 'Nieuwe toevoegen die de huidige opvolgt', 'toevoegen'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'wijzigen', 'Wijzig eigenschappen', 'bewerken'));

		if (property_exists($model::ORM, 'aanmelden_vanaf')) {
			$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'sluiten', 'Inschrijvingen nu sluiten', 'lock'));
		}

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'opvolging', 'Familienaam en groepstatus instellen', 'timeline_marker'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'converteren', 'Converteer naar ander soort groep', 'lightning'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'verwijderen', 'Definitief verwijderen (groep moet hier voor leeg zijn)', 'delete', 'confirm'));

		$this->addRowKnop(new DataTableRowKnop($model->getUrl() . 'logboek', 'Logboek bekijken', 'log'));
	}

	public function getBreadcrumbs() {
		return '<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item"><a href="/groepen">Groepen</a></li>'
			. '<li class="breadcrumb-item"><a href="' . $this->dataUrl . '">' . ucfirst($this->naam) . '</a></li>'
			. '<li class="breadcrumb-item active">Beheren</li>';
	}

	public function view() {
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		parent::view();
	}

}
