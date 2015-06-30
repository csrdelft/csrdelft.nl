<?php
require_once 'model/entity/groepen/GroepTab.enum.php';
require_once 'model/CmsPaginaModel.class.php';
require_once 'view/CmsPaginaView.class.php';
require_once 'view/GroepForms.class.php';
require_once 'view/GroepLedenView.class.php';

/**
 * GroepenView.class.php
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
		parent::__construct($model::orm, $this->url . 'beheren', null, 'familie');

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

		$create = new DataTableKnop('== 0', $this->dataTableId, $this->url . 'nieuw', 'post popup', 'Nieuw', 'Nieuwe toevoegen', 'add');
		$this->addKnop($create);

		$next = new DataTableKnop('== 1', $this->dataTableId, $this->url . 'aanmaken', 'post popup', 'Opvolger', 'Nieuwe toevoegen die de huidige opvolgt', 'add');
		$this->addKnop($next);

		$update = new DataTableKnop('== 1', $this->dataTableId, $this->url . 'wijzigen', 'post popup', 'Wijzigen', 'Wijzig eigenschappen', 'edit');
		$this->addKnop($update);

		if (property_exists($model::orm, 'aanmelden_vanaf')) {
			$sluiten = new DataTableKnop('>= 1', $this->dataTableId, $this->url . 'sluiten', 'post confirm', 'Sluiten', 'Inschrijvingen nu sluiten', 'lock');
			$this->addKnop($sluiten);
		}

		$opvolg = new DataTableKnop('>= 1', $this->dataTableId, $this->url . 'opvolging', 'post popup', 'Opvolging', 'Familienaam en groepstatus instellen', 'timeline');
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

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		// Controleer rechten
		$array = $groep->jsonSerialize();

		$array['detailSource'] = $groep->getUrl() . 'leden';

		$title = $groep->naam;
		if (!empty($groep->samenvatting)) {
			$title .= '&#13;&#13;' . mb_substr($groep->samenvatting, 0, 100);
			if (strlen($groep->samenvatting) > 100) {
				$title .= '...';
			}
		}
		$array['naam'] = '<span title="' . $title . '">' . $groep->naam . '</span>';
		$array['status'] = GroepStatus::getChar($groep->status);
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['maker_uid'] = null;

		if (property_exists($groep, 'in_agenda')) {
			$array['in_agenda'] = $groep->in_agenda ? 'ja' : 'nee';
		}

		return parent::getJson($array);
	}

}

class GroepLogboekTable extends DataTable implements FormElement {

	public function __construct(AbstractGroep $groep) {
		require_once 'model/entity/ChangeLogEntry.class.php';
		parent::__construct(ChangeLogModel::orm, $groep->getUrl() . 'logboek', false, 'moment');
		$this->hideColumn('subject');
		$this->searchColumn('property');
		$this->searchColumn('old_value');
		$this->searchColumn('new_value');
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Door');
	}

	public function getHtml() {
		throw new Exception('not implemented');
	}

	public function getType() {
		return get_class($this);
	}

}

class GroepLogboekData extends DataTableResponse {

	public function getJson($log) {
		$array = $log->jsonSerialize();

		$array['uid'] = ProfielModel::getLink($log->uid, 'civitas');

		return parent::getJson($array);
	}

}

class GroepView implements FormElement {

	private $groep;
	private $leden;
	private $geschiedenis;
	private $bb;

	public function __construct(AbstractGroep $groep, $tab = null, $geschiedenis = false, $bb = false) {
		$this->groep = $groep;
		$this->geschiedenis = $geschiedenis;
		$this->bb = $bb;
		switch ($tab) {

			case GroepTab::Pasfotos:
				$this->leden = new GroepPasfotosView($groep);
				break;

			case GroepTab::Lijst:
				$this->leden = new GroepLijstView($groep);
				break;

			case GroepTab::Statistiek:
				$this->leden = new GroepStatistiekView($groep);
				break;

			case GroepTab::Emails:
				$this->leden = new GroepEmailsView($groep);
				break;

			case GroepTab::Emails:
				$this->leden = new GroepEmailsView($groep);
				break;

			case GroepTab::Eetwens:
				$this->leden = new GroepEetwensView($groep);
				break;

			default:
				if ($groep->keuzelijst) {
					$this->leden = new GroepLijstView($groep);
				} else {
					$this->leden = new GroepPasfotosView($groep);
				}
		}
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getHtml() {
		$html = '<a name="' . $this->groep->id . '"></a><div id="groep-' . $this->groep->id . '" class="bb-groep';
		if ($this->geschiedenis) {
			$html .= ' state-geschiedenis';
		}
		if ($this->bb) {
			$html .= ' bb-block';
		}
		$html .= '"><div id="groep-samenvatting-' . $this->groep->id . '" class="groep-samenvatting">';
		if ($this->groep->mag(A::Wijzigen)) {
			$html .= '<div class="float-right"><a class="btn" href="' . $this->groep->getUrl() . 'wijzigen' . '" title="Wijzig ' . htmlspecialchars($this->groep->naam) . '"><span class="fa fa-pencil"></span></a></div>';
		}
		$html .= '<h3>' . $this->getTitel();
		if (property_exists($this->groep, 'locatie') AND ! empty($this->groep->locatie)) {
			$html .= ' &nbsp; <a target="_blank" href="https://maps.google.nl/maps?q=' . urlencode($this->groep->locatie) . '" title="' . $this->groep->locatie . '" class="lichtgrijs fa fa-map-marker fa-lg"></a>';
		}
		$html .= '</h3>';
		$html .= CsrBB::parse($this->groep->samenvatting);
		if (!empty($this->groep->omschrijving)) {
			$html .= '<div class="clear">&nbsp;</div><a id="groep-omschrijving-' . $this->groep->id . '" class="post noanim" href="' . $this->groep->getUrl() . 'omschrijving">Meer lezen »</a>';
		}
		$html .= '</div>';
		$html .= $this->leden->getHtml();
		$html .= '<div class="clear">&nbsp</div></div>';
		return $html;
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getJavascript() {
		return null;
	}

	public function getType() {
		return get_class($this->groep);
	}

}

class GroepenView implements View {

	private $model;
	private $groepen;
	private $soort;
	private $geschiedenis;
	private $tab;
	private $pagina;

	public function __construct(AbstractGroepenModel $model, $groepen, $soort = null, $geschiedenis = false) {
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
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getModel() {
		return $this->groepen;
	}

	public function getTitel() {
		return $this->pagina->titel;
	}

	public function view() {
		$model = $this->model;
		$orm = $model::orm;
		if ($orm::magAlgemeen(A::Aanmaken, $this->soort)) {
			echo '<a class="btn" href="' . $this->model->getUrl() . 'nieuw/' . $this->soort . '"><img class="icon" src="/plaetjes/famfamfam/add.png" width="16" height="16"> Toevoegen</a>';
		}
		echo '<a class="btn" href="' . $this->model->getUrl() . 'beheren"><img class="icon" src="/plaetjes/famfamfam/table.png" width="16" height="16"> Beheren</a>';
		if ($this->geschiedenis) {
			echo '<a id="deelnamegrafiek" class="btn post" href="' . $this->model->getUrl() . $this->geschiedenis . '/deelnamegrafiek"><img class="icon" src="/plaetjes/famfamfam/chart_bar.png" width="16" height="16"> Deelnamegrafiek</a>';
		}
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->groepen as $groep) {
			// Controleer rechten
			if (!$groep->mag(A::Bekijken)) {
				continue;
			}
			echo '<hr>';
			$view = new GroepView($groep, $this->tab, $this->geschiedenis);
			$view->view();
		}
	}

}

class GroepenDeelnameGrafiek implements View {

	private $series = array();
	private $step = array();

	public function __construct($groepen) {
		$mannen = array();
		$vrouwen = array();
		$smallest_diff = PHP_INT_MAX;
		$previous_time = 0;
		foreach ($groepen as $groep) {
			$time = strtotime($groep->begin_moment);
			$smallest_diff = min($smallest_diff, abs($time - $previous_time));
			$previous_time = $time;
			if (!isset($mannen[$time])) {
				$mannen[$time] = 0;
				$vrouwen[$time] = 0;
			}
			foreach ($groep->getLeden() as $lid) {
				$profiel = ProfielModel::get($lid->uid);
				if ($profiel->geslacht === Geslacht::Man) {
					$mannen[$time] += 1;
				} else {
					$vrouwen[$time] += 1;
				}
			}
		}
		$year = 365 * 24 * 60 * 60;
		$month = 30 * 24 * 60 * 60;
		$day = 24 * 60 * 60;
		if ($smallest_diff >= $year) {
			$this->step[] = floor($smallest_diff / $year);
			$this->step[] = 'year';
		} elseif ($smallest_diff >= $month) {
			$this->step[] = floor($smallest_diff / $month);
			$this->step[] = 'month';
		} else {
			$this->step[] = floor($smallest_diff / $day);
			$this->step[] = 'day';
		}
		foreach ($mannen as $time => $aantal) {
			$this->series[0][] = array($time * 1000, $aantal);
			$this->series[1][] = array($time * 1000, $vrouwen[$time]);
		}
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->series;
	}

	public function getTitel() {
		return null;
	}

	public function view() {
		?>
		<div id="deelnamegrafiek" style="height: 360px;">
			<script type="text/javascript">
				$(document).ready(function () {
					var series = [{
							data: <?= json_encode($this->series[1]); ?>,
							label: "",
							color: "#FFCBDB"
						}, {
							data: <?= json_encode($this->series[0]); ?>,
							label: "",
							color: "#AFD8F8"
						}
					];
					var options = {
						series: {
							bars: {
								show: true,
								lineWidth: 20
							},
							stack: true
						}, yaxis: {
							tickDecimals: 0
						},
						xaxis: {
							autoscaleMargin: .01
						},
						xaxes: [{
								mode: "time",
								minTickSize: <?= json_encode($this->step); ?>
							}]
					};
					$.plot("#deelnamegrafiek", series, options);
				});
			</script>
		</div>
		<?php
	}

}
