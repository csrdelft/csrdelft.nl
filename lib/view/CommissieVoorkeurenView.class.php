<?php

class CommissieVoorkeurenView implements View {

	private $id;

	public function __construct($id = -1) {
		$this->id = $id;
	}

	public function getModel() {
		$this->id;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><img src="/plaetjes/knopjes/people-16.png" class="module-icon"></a> » <a href="/commissievoorkeuren">' . $this->getTitel() . '</a>';
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
				echo '<tr ' . ($voorkeur['gedaan'] ? 'style="opacity: .50"' : '') . '><td><a href="/commissievoorkeuren/lidpagina/' . $uid . '">' . LidCache::getLid($uid)->getNaam() . '</a></td><td>' . $format[$voorkeur['voorkeur']] . '</td></tr>';
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

class LidOverzicht implements View {

	private $id;

	public function __construct($id) {
		$this->id = $id;
	}

	public function getModel() {
		return $this->id;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><img src="/plaetjes/knopjes/people-16.png" class="module-icon"></a> » <a href="/commissievoorkeuren">Voorkeuren voor commissies</a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return 'Voorkeur van lid';
	}

	public function view() {
		echo '<h1>' . $this->getTitel() . ' </h1>';
		echo '<p>Naam: ' . Lid::naamLink($this->id, 'volledig', 'link') . '</p>';
		$voorkeur = new CommissieVoorkeurenModel($this->id);
		$voorkeuren = $voorkeur->getVoorkeur();
		$commissies = $voorkeur->getCommissies();
		echo '<table>';
		$opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');
		foreach ($voorkeuren as $cid => $voork) {
			echo '<tr><td>' . $commissies[$cid] . '</td><td>' . $opties[$voork] . '</td></tr>';
		}
		echo '</table><br />';
		echo '<h3>Lid opmerkingen</h3><p>' . $voorkeur->getLidOpmerking() . '</p>';
		echo '
		<form name="opties" action="/commissievoorkeuren/lidpagina/' . $this->id . '" method="POST">
			<textarea name = "opmerkingen" cols=40 rows = 10 >' . $voorkeur->getPraesesOpmerking() . ' </textarea> <br />
			<input type="submit" value="Opslaan" />
		</form>
		';
	}

}
