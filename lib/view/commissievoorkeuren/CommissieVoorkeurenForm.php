<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurenModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieCategorieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurVoorkeur;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class CommissieVoorkeurenForm extends Formulier {

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » ' . $this->profiel->getLink('civitas') . ' » <span class="active">' . $this->titel . '</span>';
	}

	private $voorkeuren = array();
	private $opmerking;
	private $profiel;

	public function __construct(Profiel $profiel) {
		parent::__construct(null, '/profiel/' . $profiel->uid . '/voorkeuren', 'Commissie-voorkeuren');
		$this->profiel = $profiel;
		$this->addFields([new HtmlComment('<p>Hier kunt u per commissie opgeven of u daar interesse in heeft!</p>')]);
		$categorieCommissie = VoorkeurCommissieModel::instance()->getByCategorie();

		foreach ($categorieCommissie as $cat) {
			$categorie = $cat['categorie'];
			$this->addFields([new Subkopje($categorie->naam)]);
			foreach ($cat['commissies'] as $commissie) {
				if ($commissie->zichtbaar) {
					$this->addVoorkeurVeld($commissie);
				}
			}
		}

		$this->opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid($profiel);

		$fields[] = new Subkopje("Extra opmerkingen");
		$opmerkingVeld = new TextareaField('lidOpmerking', $this->opmerking->lidOpmerking, 'Vul hier je eventuele voorkeur voor functie in, of andere opmerkingen');
		$this->opmerking->lidOpmerking = $opmerkingVeld->getValue();
		$fields[] = $opmerkingVeld;

		$fields[] = new FormDefaultKnoppen('/profiel/' . $profiel->uid);

		$this->addFields($fields);
	}

	private function addVoorkeurVeld(VoorkeurCommissie $commissie) {
		$opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');
		$voorkeur = CommissieVoorkeurModel::instance()->getVoorkeur($this->profiel, $commissie);
		$this->voorkeuren[] = $voorkeur;
		$field = new SelectField('comm' . $commissie->id, $voorkeur->voorkeur, $commissie->naam, $opties);
		$this->addFields([$field]);
		$voorkeur->voorkeur = $field->getValue();
	}

	public function getVoorkeuren(): array {
		return $this->voorkeuren;
	}

	public function getOpmerking() {
		return $this->opmerking;
	}

}
