<?php

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\gesprekken\GesprekBerichtenModel;
use CsrDelft\view\View;

/**
 * GesprekkenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GesprekkenView implements View {

	private $gesprek;
	private $gesprekkenTable;
	private $berichtenTable;
	private $berichtForm;

	public function __construct(Gesprek $gesprek = null, $filter = null) {
		if ($gesprek) {
			$this->gesprek = $gesprek;
			GesprekBerichtenModel::instance();
			$this->berichtenTable = new GesprekBerichtenTable($gesprek);
			$this->berichtForm = new GesprekBerichtForm($gesprek, $this->berichtenTable->getDataTableId());
		} else {
			$this->gesprekkenTable = new GesprekkenTable();
			$this->gesprekkenTable->filter = $filter;
		}
	}

	public function getBreadcrumbs() {
		if ($this->gesprek) {
			$gesprek = $this->berichtenTable->getTitel();
		} else {
			$gesprek = 'Gesprekken';
		}
		return '<a href="/gesprekken" title="Gesprekken"><span class="fa fa-envelope-o module-icon"></span></a> » <span class="active">' . $gesprek . '</span></div>';
	}

	public function getModel() {
		return null;
	}

	public function getTitel() {
		return 'Gesprekken';
	}

	public function view() {
		echo getMelding();
		if ($this->gesprek) {
			echo '<div class="GesprekBerichten">';
			$this->berichtenTable->view();
			$this->berichtForm->view();
		} else {
			echo '<div class="Gesprekken">';
			echo '<h1>' . $this->getTitel() . '</h1>';
			$this->gesprekkenTable->view();
		}
		echo '</div>';
	}

}
