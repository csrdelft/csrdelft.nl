<?php
namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\model\ProfielModel;
use CsrDelft\model\VoorkeurCommissie;
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
			$commissie = VoorkeurCommissie::getCommissie($this->id);
			echo '<h1>' . $commissie->getNaam() . ' </h1>';
			echo '<table><tr><td><h4>Lid</h4></td><td><h4>Interesse</h4></td></tr>';
			$geinteresseerde = $commissie->getGeinteresseerde();
			foreach ($geinteresseerde as $uid => $voorkeur) {
				echo '<tr ' . ($voorkeur['gedaan'] ? 'style="opacity: .50"' : '') . '><td><a href="/commissievoorkeuren/lidpagina/' . $uid . '">' . ProfielModel::get($uid)->getNaam() . '</a></td><td>' . $format[$voorkeur['voorkeur']] . '</td></tr>';
			}
			echo '</table>';
		} else {
			echo '<h1>' . $this->getTitel() . ' </h1>';
			echo '<p>klik op een commissie om de voorkeuren te bekijken';
			echo '<ul>';
			foreach (VoorkeurCommissie::getCommissies() as $cid => $naam) {
				echo '<li> <a href="/commissievoorkeuren/overzicht/' . $cid . '" >' . $naam . '</a></li>';
			}
			echo '</ul>';
		}
	}

}
