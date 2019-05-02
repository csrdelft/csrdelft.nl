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
		$uid = LoginModel::getUid();

		// Controleer rechten
		if (!$groep->mag(AccessAction::Bekijken)) {
			return '';
		}
		if ($groep->versie == GroepVersie::V2) {
			$settings = [
				'mijn_uid' => $uid,
				'mijn_link' => ProfielModel::getLink($uid),
				'aanmeld_url' => $groep->getUrl() . 'aanmelden2/' . $uid,
			];

			return vsprintf('<groep class="vue-context" :groep="%s" :settings="%s"></groep>', [
				vue_encode($groep),
				vue_encode($settings)
			]);
		}
		$view = new GroepView($groep, null, false, true);
		return $view->getHtml();
	}
}
