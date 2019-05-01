<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\model\entity\groepen\GroepKeuzeSelectie;
use CsrDelft\model\entity\groepen\GroepKeuzeType;
use CsrDelft\model\entity\groepen\GroepVersie;
use CsrDelft\model\entity\groepen\KetzerDeelnemer;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\CsrBbException;
use CsrDelft\view\groepen\GroepView;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
abstract class BbTagGroep extends BbTag {
	public function parseLight($arguments = []) {
		if (isset($arguments[$this->getTagName()])) {
			$id = $arguments[$this->getTagName()];
		} else {
			$id = $this->getContent();
		}
		$groep = $this->getModel()::get($id);
		if ($groep) {
			return $this->groepLight($groep, 'ketzer', $this->getLidNaam());
		} else {
			$url = $this->getModel()::getUrl();
			return ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="' . $url . 'beheren">Zoeken</a>';
		}
	}

	/**
	 * @return AbstractGroepenModel
	 */
	abstract public function getModel();

	protected function groepLight(AbstractGroep $groep, $tag, $leden) {
		if (!$groep->mag(AccessAction::Bekijken)) {
			return '';
		}
		return $this->lightLinkBlock($tag, $groep->getUrl(), $groep->naam, $groep->aantalLeden() . ' ' . $leden);
	}

	abstract public function getLidNaam();

	public function parse($arguments = []) {
		$id = $this->getArgument($arguments);
		$groep = $this->getModel()::get($id);
		if (!$groep) {
			$url = $this->getModel()::getUrl();
			throw new CsrBbException(ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="' . $url . '/beheren">Zoeken</a>');
		}
		return $this->groep($groep);
	}

	protected function groep(AbstractGroep $groep) {
		$keuze1 = new GroepKeuze('keuze-1', GroepKeuzeType::CHECKBOX, true, 'Keuze 1');
		$keuze2 = new GroepKeuze('keuze-2', GroepKeuzeType::CHECKBOX, true, 'Keuze 2');
		$keuze3 = new GroepKeuze('keuze-3', GroepKeuzeType::CHECKBOX, true, 'Keuze 3');
		$keuze4 = new GroepKeuze('keuze-4', GroepKeuzeType::CHECKBOX, true, 'Keuze 4');

		$lid1 = new KetzerDeelnemer();
		$lid1->uid = '1346';
		$lid1->opmerking = [
			new GroepKeuzeSelectie('keuze-1', false),
			new GroepKeuzeSelectie('keuze-2', true),
			new GroepKeuzeSelectie('keuze-3', true),
			new GroepKeuzeSelectie('keuze-4', true),
		];
		return sprintf('<groep class="vue-context" :groep="%s" :settings="%s"></groep>', htmlspecialchars(json_encode([
			'id' => 1,
			'mijn_uid' => LoginModel::getUid(),
			'naam' => 'Mijn groep',
			'familie' => 'fam',
			'begin_moment' => date('Y'),
			'eind_moment' => date('Y'),
			'status' => 'ht',
			'samenvatting' => 'Dit is mijn groep',
			'omschrijving' => 'Lees meer',
			'keuzelijst' => null,
			'maker_uid' => '1345',
			'versie' => 'v2',
			'keuzelijst2' => [$keuze1, $keuze2, $keuze3, $keuze4],
			'leden' => [$lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1, $lid1],
		])), htmlspecialchars(json_encode(['mijn_uid' => LoginModel::getUid(), 'mijn_link' => ProfielModel::getLink(LoginModel::getUid())])));

		// Controleer rechten
		if (!$groep->mag(AccessAction::Bekijken)) {
			return '';
		}
		if ($groep->versie == GroepVersie::V2) {
			return sprintf('<groep :settings="%s"></groep>', json_encode($groep));
		}
		$view = new GroepView($groep, null, false, true);
		return $view->getHtml();
	}
}
