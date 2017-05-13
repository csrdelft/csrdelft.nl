<?php
namespace CsrDelft\view\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\view\CmsPaginaView;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;


/**
 * GroepenBeheerTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GroepenBeheerTable extends DataTable {

	private $url;
	private $naam;
	private $pagina;

	public function __construct(AbstractGroepenModel $model) {
		$this->url = $model->getUrl();
		parent::__construct($model::ORM, $this->url . 'beheren', null, 'familie');

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

		$preview = new DataTableKnop('== 1', $this->dataTableId, $this->url . 'voorbeeld', 'post popup', 'Voorbeeld', 'Voorbeeldweergave van de ketzer', 'show');
		$this->addKnop($preview);

		$create = new DataTableKnop('== 0', $this->dataTableId, $this->url . 'nieuw', 'post popup', 'Nieuw', 'Nieuwe toevoegen', 'toevoegen');
		$this->addKnop($create);

		$next = new DataTableKnop('== 1', $this->dataTableId, $this->url . 'aanmaken', 'post popup', 'Opvolger', 'Nieuwe toevoegen die de huidige opvolgt', 'toevoegen');
		$this->addKnop($next);

		$update = new DataTableKnop('== 1', $this->dataTableId, $this->url . 'wijzigen', 'post popup', 'Wijzigen', 'Wijzig eigenschappen', 'bewerken');
		$this->addKnop($update);

		if (property_exists($model::ORM, 'aanmelden_vanaf')) {
			$sluiten = new DataTableKnop('>= 1', $this->dataTableId, $this->url . 'sluiten', 'post confirm', 'Sluiten', 'Inschrijvingen nu sluiten', 'lock');
			$this->addKnop($sluiten);
		}

		$opvolg = new DataTableKnop('>= 1', $this->dataTableId, $this->url . 'opvolging', 'post popup', 'Opvolging', 'Familienaam en groepstatus instellen', 'timeline_marker');
		$this->addKnop($opvolg);

		$convert = new DataTableKnop('>= 1', $this->dataTableId, $this->url . 'converteren', 'post popup', 'Converteren', 'Converteer naar ander soort groep', 'lightning');
		$this->addKnop($convert);

		$delete = new DataTableKnop('>= 1', $this->dataTableId, $this->url . 'verwijderen', 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);

		$log = new DataTableKnop('== 1', $this->dataTableId, $this->url . 'logboek', 'post popup', 'Logboek', 'Logboek bekijken', 'log');
		$this->addKnop($log);
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <a href="' . $this->url . '">' . ucfirst($this->naam) . '</a> » <span class="active">Beheren</span>';
	}

	public function view() {
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		parent::view();
	}

}
