<?php
namespace CsrDelft\view;
use CsrDelft\Icon;
use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;
use CsrDelft\view\formulier\datatable\DataTableResponse;
use CsrDelft\view\formulier\elementen\FormElement;
use function CsrDelft\group_by_distinct;

/**
 * GroepLedenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepLedenTable extends DataTable {

	public function __construct(AbstractGroepLedenModel $model, AbstractGroep $groep) {
		parent::__construct($model::ORM, $groep->getUrl() . 'leden', 'Leden van ' . $groep->naam, 'status');
		$this->hideColumn('uid', false);
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Lidnaam');
		$this->setColumnTitle('door_uid', 'Aangemeld door');

		if ($groep->mag(AccessAction::Beheren)) {

			$create = new DataTableKnop('== 0', $this->dataTableId, $groep->getUrl() . 'aanmelden', 'post popup', 'Aanmelden', 'Lid toevoegen', 'user_add');
			$this->addKnop($create);

			$update = new DataTableKnop('== 1', $this->dataTableId, $groep->getUrl() . 'bewerken', 'post popup', 'Bewerken', 'Lidmaatschap bewerken', 'user_edit');
			$this->addKnop($update);

			$delete = new DataTableKnop('>= 1', $this->dataTableId, $groep->getUrl() . 'afmelden', 'post confirm', 'Afmelden', 'Leden verwijderen', 'user_delete');
			$this->addKnop($delete);
		}
	}

}

class GroepLedenData extends DataTableResponse {

	public function getJson($lid) {
		$array = $lid->jsonSerialize();

		$array['uid'] = ProfielModel::getLink($array['uid'], 'civitas');
		$array['door_uid'] = ProfielModel::getLink($array['door_uid'], 'civitas');

		return parent::getJson($array);
	}

}

class GroepStatistiekView extends groepen\leden\GroepTabView {

	private function verticale($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array(
				'label'	 => $row[0],
				'data'	 => $row[1]
			);
		}
		$this->javascript .= <<<JS

	series: {
		pie: {
			show: true,
			radius: 1,
			label: {
				show: true,
				radius: 2/3,
				formatter: function(label, series) {
					return '<div class="pie-chart-label">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
				},
				threshold: 0.1
			}
		}
	},
	legend: {
		show: false
	}
JS;
		return $series;
	}

	private function geslacht($data) {
		$series = array();
		foreach ($data as $row) {
			switch ($row[0]) {

				case 'm':
					$series[] = array(
						'label'	 => '',
						'data'	 => $row[1],
						'color'	 => '#AFD8F8'
					);
					break;

				case 'v':
					$series[] = array(
						'label'	 => '',
						'data'	 => $row[1],
						'color'	 => '#FFCBDB'
					);
					break;
			}
		}
		$this->javascript .= <<<JS

	series: {
		pie: {
			show: true,
			radius: 1,
			innerRadius: .5,
			label: {
				show: false
			}
		}
	},
	legend: {
		show: false
	}
JS;
		return $series;
	}

	private function lichting($data) {
		$series = array();
		foreach ($data as $row) {
			$series[] = array('data' => array(array((int) $row[0], (int) $row[1])));
		}
		$this->javascript .= <<<JS

	series: {
		bars: {
			show: true,
			barWidth: 0.5,
			align: "center",
			lineWidth: 0,
			fill: 1
		}
	},
	xaxis: {
		tickDecimals: 0
	},
	yaxis: {
		tickDecimals: 0
	}
JS;
		return $series;
	}

	private function tijd($data) {
		$series = array();
		$totaal = 0;
		foreach ($data as $tijd => $aantal) {
			$totaal += $aantal;
			$series[0][] = array($tijd, $totaal);
		}
		$this->javascript .= <<<JS

	xaxes: [{
		mode: "time"
	}],
	yaxis: {
		tickDecimals: 0
	}
JS;
		return $series;
	}

	public function getTabContent() {
		$html = '';

		foreach ($this->groep->getStatistieken() as $titel => $data) {
			$html .= '<h4>' . $titel . '</h4>';
			if (!is_array($data)) {
				$html .= $data;
				continue;
			}
			$html .= '<div id="groep-stat-' . $titel . '-' . $this->groep->id . '" class="groep-stat"></div>';
			$this->javascript .= <<<JS

var div = $("#groep-stat-{$titel}-{$this->groep->id}");
div.height(div.width());
$.plot(div, data{$titel}{$this->groep->id}, {
JS;
			switch ($titel) {

				case 'Verticale': $series = $this->verticale($data);
					break;

				case 'Geslacht': $series = $this->geslacht($data);
					break;

				case 'Lichting': $series = $this->lichting($data);
					break;

				case 'Tijd': $series = $this->tijd($data);
					break;
			}
			// prepend data
			$data = json_encode($series);
			$this->javascript = <<<JS

var data{$titel}{$this->groep->id} = {$data};
{$this->javascript}
});
JS;
		}
		return $html;
	}

}

class GroepEmailsView extends groepen\leden\GroepTabView {

	public function getTabContent() {
		$html = '';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$html .= $profiel->getPrimaryEmail() . '; ';
			}
		}
		return $html;
	}

}

class GroepEetwensView extends groepen\leden\GroepTabView {

	public function getTabContent() {
		$html = '<table class="groep-lijst"><tbody>';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->eetwens != '') {
				$html .= '<tr><td>' . $profiel->getLink() . '</td><td>' . $profiel->eetwens . '</td></tr>';
			}
		}
		return $html . '</tbody></table>';
	}

}
