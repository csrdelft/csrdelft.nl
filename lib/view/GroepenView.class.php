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

	public function __construct(GroepenModel $model) {
		parent::__construct($model::orm, null, 'familie');

		$this->url = $model->getUrl();
		$this->dataUrl = $this->url . 'beheren';

		$this->naam = $model->getNaam();
		$this->titel = 'Beheer ' . $this->naam;

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

		$create = new DataTableKnop('== 0', $this->tableId, $this->url . 'nieuw', 'post popup', 'Nieuw', 'Nieuwe toevoegen', 'add');
		$this->addKnop($create);

		$next = new DataTableKnop('== 1', $this->tableId, $this->url . 'aanmaken', 'post popup', 'Opvolger', 'Nieuwe toevoegen die de huidige opvolgt', 'add');
		$this->addKnop($next);

		$update = new DataTableKnop('== 1', $this->tableId, $this->url . 'wijzigen', 'post popup', 'Wijzigen', 'Wijzig eigenschappen', 'edit');
		$this->addKnop($update);

		if (property_exists($model::orm, 'aanmelden_vanaf')) {
			$sluiten = new DataTableKnop('>= 1', $this->tableId, $this->url . 'sluiten', 'post confirm', 'Sluiten', 'Inschrijvingen nu sluiten', 'lock');
			$this->addKnop($sluiten);
		}

		$opvolg = new DataTableKnop('>= 1', $this->tableId, $this->url . 'opvolging', 'post popup', 'Opvolging', 'Familienaam en groepstatus instellen', 'timeline');
		$this->addKnop($opvolg);

		$convert = new DataTableKnop('>= 1', $this->tableId, $this->url . 'converteren', 'post popup', 'Converteren', 'Converteer naar ander soort groep', 'lightning');
		$this->addKnop($convert);

		$delete = new DataTableKnop('>= 1', $this->tableId, $this->url . 'verwijderen', 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

	public function getBreadcrumbs() {
		return '<a href="/groepen" title="Groepen"><span class="fa fa-users module-icon"></span></a> » <a href="' . $this->url . '">' . ucfirst($this->naam) . '</a> » <span class="active">Beheren</span>';
	}

	public function view() {
		$view = new CmsPaginaView(CmsPaginaModel::get($this->naam));
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

		return parent::getJson($array);
	}

}

class GroepView implements View {

	private $groep;
	private $leden;
	private $bb;

	public function __construct(Groep $groep, $tab = null, $bb = false) {
		$this->groep = $groep;
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
		$html = '<div id="groep-' . $this->groep->id . '" class="bb-groep';
		if ($this->bb) {
			$html .= ' bb-block';
		}
		if ($this->groep->maker_uid == 1025 AND $this->bb) {
			$html .= ' bb-dies2015';
		}
		$html .= '"><div id="groep-samenvatting-' . $this->groep->id . '" class="groep-samenvatting"><h3>' . $this->getTitel() . '</h3>';
		if ($this->groep->maker_uid == 1025) {
			$html .= '<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M" class="float-left" style="margin-right: 10px;">';
		}
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

}

class GroepenView implements View {

	private $model;
	private $groepen;
	private $soort;
	private $geschiedenis;
	private $pagina;
	private $tab;

	public function __construct(GroepenModel $model, $groepen, $soort = null, $geschiedenis = false) {
		$this->model = $model;
		$this->groepen = $groepen;
		$this->soort = $soort;
		$this->geschiedenis = $geschiedenis;
		$this->pagina = CmsPaginaModel::get($model->getNaam());
		if ($model instanceof BesturenModel) {
			$this->tab = GroepTab::Lijst;
		} else {
			$this->tab = GroepTab::Pasfotos;
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
			$view = new GroepView($groep, $this->tab);
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
