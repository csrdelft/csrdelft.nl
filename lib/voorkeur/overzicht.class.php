<?php

class CommissieOverzicht extends SmartyTemplateView {

	public function __construct($id = -1) {
		parent::__construct($id);
	}

	public function getTitel() {
		return 'voorkeuren voor commissies';
	}

	function view() {
		$res = '';
		if (LoginModel::mag('P_LEDEN_MOD')) {
			require_once 'voorkeur/commissie.class.php';
			if ($this->model >= 0) {
				$commissie = OldCommissie::getCommissie($this->model);
				$res .= '<h1> Geinteresseerde voor ' . $commissie->getNaam() . ' </h1> 
					<a href="/tools/voorkeuren/commissies.php">Terug naar overzicht</a>
					<table><tr><td><h3>Lid</h3></td><td><h3>Interesse</h3></td></tr>';
				$geinteresseerde = $commissie->getGeinteresseerde();
				foreach ($geinteresseerde as $uid => $voorkeur) {
					$res .= '<tr ' . ($voorkeur['gedaan'] ? 'style="opacity: .50"' : '') . '><td><a href="/tools/voorkeuren/lidpagina.php?lid=' . $uid . '">' . LidCache::getLid($uid)->getNaam() . '</a></td><td>' . voorkeur($voorkeur['voorkeur']) . '</td></tr>';
				}
				$res .= '</table>';
			} else {
				$commissies = OldCommissie::getCommissies();
				$res .= '<h1>Commissie voorkeuren leden</h1>';
				$res .= '<p>klik op een commissie om de voorkeuren te bekijken';
				$res .= '<ul>';
				foreach ($commissies as $cid => $naam) {
					$res .= '<li> <a href="commissies.php?c=' . $cid . '" >' . $naam . '</a></li>';
				}
				$res .= '</ul>';
			}
		} else {
			$res = 'geen permissie om deze pagina te bekijken!';
		}
		return $res;
	}

}

function voorkeur($voorkeur) {
	$arr = array('', 'nee', 'misschien', 'ja');
	return $arr[$voorkeur];
}

class LidOverzicht extends SmartyTemplateView {

	public function __construct($id = -1) {
		parent::__construct($id);
	}

	public function getTitel() {
		return 'voorkeur van lid';
	}

	function view() {
		$res = '';
		if ($this->model == -1) {
			$res = $this->viewNotAllowed();
		} else {
			$res = $this->viewProfile();
		}
		return $res;
	}

	function viewNotAllowed() {
		return 'U mag deze pagina niet bekijken';
	}

	function viewProfile() {
		$res = '<h1> Voorkeuren!</h1>';
		require_once 'voorkeur/lidvoorkeur.class.php';
		$res .= '<p>Naam: ' . Lid::naamLink($this->model, 'full', 'link') . '</p>';
		$voorkeur = new LidVoorkeur($this->model);
		$voorkeuren = $voorkeur->getVoorkeur();
		$commissies = $voorkeur->getCommissies();
		$res .= '<table>';
		$opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');
		foreach ($voorkeuren as $cid => $voork) {
			$res .= '<tr><td>' . $commissies[$cid] . '</td><td>' . $opties[$voork] . '</td></tr>';
		}
		$res .= '</table><br />';
		$res .= '<h2>Lid opmerkingen</h2><p>' . $voorkeur->getLidOpmerking() . '</p>';
		$res .= '
		<form name="opties" action="/tools/voorkeuren/lidpagina.php?lid=' . $this->model . '" method="POST">
			<textarea name = "opmerkingen" cols=40 rows = 10 >' . $voorkeur->getPraesesOpmerking() . ' </textarea> <br />
			<input type="submit" value="Opslaan" />
		</form>
		';
		$res .= '<a href="/tools/voorkeuren/commissies.php">commissie overzicht</a>';
		return $res;
	}

	function save($actie) {
		require_once 'voorkeur/lidvoorkeur.class.php';
		$voorkeur = new LidVoorkeur($this->model);
		$voorkeur->setPraesesOpmerking($actie);
	}

}
