<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\controller\CommissieVoorkeurenController;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurenModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\View;

class CommissieVoorkeurenProfiel implements View
{

	/**
	 * @var Profiel
	 */
	private $profiel;

	public function __construct($profiel)
	{
		$this->profiel = $profiel;
	}

	public function getModel()
	{
		return $this->profiel;
	}

	public function getBreadcrumbs()
	{
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">Voorkeuren voor commissies</a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel()
	{
		return 'Voorkeur van lid';
	}

	public function view()
	{
		echo '<h1>' . $this->getTitel() . ' </h1>';
		echo '<p>Naam: ' . $this->profiel->getLink('volledig') . '</p>';

		$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid($this->profiel);
		$voorkeuren = CommissieVoorkeurModel::instance()->getVoorkeurenVoorLid($this->profiel);
		$voorkeurenMap = array();
		foreach ($voorkeuren as $voorkeur) {
			$voorkeurenMap[$voorkeur->cid] = $voorkeur;
		}
		$commissies = VoorkeurCommissieModel::instance()->find("zichtbaar = 1");
		echo '<table>';
		$opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');
		foreach ($commissies as $commissie) {
			$voorkeur = $voorkeurenMap[$commissie->id];
			echo '<tr><td>' . $commissie->naam . '</td><td>' . $opties[$voorkeur === null ? 1 : $voorkeur->voorkeur] . '</td></tr>';
		}
		echo '</table><br />';
		echo '<h3>Lid opmerkingen</h3><p>' . $opmerking->lidOpmerking . '</p>';
		echo '
		<form name="opties" action="/commissievoorkeuren/lidpagina/' . $this->profiel->uid . '" method="POST">
			<textarea name = "praeses-opmerking" cols=40 rows = 10 >' . $opmerking->praesesOpmerking . '</textarea> <br />
			<input type="submit" value="Opslaan" />
		</form>
		';
	}

}
