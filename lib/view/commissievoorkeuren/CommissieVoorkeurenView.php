<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\View;

class CommissieVoorkeurenView implements View {

	private $id;

	public function __construct($id = -1) {
		$this->id = $id;
	}

	public function getModel() {
		$this->id;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">' . $this->getTitel() . '</a>';
	}

	public function getTitel() {
		return 'Voorkeuren voor commissies';
	}

	public function view() {
		$format = array('', 'nee', 'misschien', 'ja');
		if ($this->id >= 0) {
			$commissie = VoorkeurCommissieModel::instance()->retrieveByUUID($this->id);
			echo '<h1>' . $commissie->naam . ' </h1>';
			echo '<table><tr><td><h4>Lid</h4></td><td><h4>Interesse</h4></td></tr>';
			$voorkeuren = CommissieVoorkeurModel::instance()->getVoorkeurenVoorCommissie($commissie, 2);
			foreach ($voorkeuren as $voorkeur) {
				echo '<tr ' . ($voorkeur->heeftGedaan() ? 'style="opacity: .50"' : '') . '><td><a href="/commissievoorkeuren/lidpagina/' . $voorkeur->uid . '">' . $voorkeur->getProfiel()->getNaam() . '</a></td><td>' . $format[$voorkeur->voorkeur] . '</td></tr>';
			}
			echo '</table>';
		} else {
			echo '<h1>' . $this->getTitel() . ' </h1>';
			echo '<p>klik op een commissie om de voorkeuren te bekijken';
			echo '<ul>';
			foreach (VoorkeurCommissieModel::instance()->find() as $commissie) {
				echo '<li> <a href="/commissievoorkeuren/overzicht/' . $commissie->id . '" >' . $commissie->naam . '</a></li>';
			}
			echo '</ul>';
		}
	}

}
