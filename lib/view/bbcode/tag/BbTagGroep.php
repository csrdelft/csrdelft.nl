<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\GroepVersie;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\groepen\GroepView;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
abstract class BbTagGroep extends BbTag {

	public function isAllowed()
	{
		return $this->getGroep()->mag(AccessAction::Bekijken);
	}

	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}

	public function renderLight() {
		$groep = $this->getGroep();
		if ($groep) {
			return $this->groepLight($groep, 'ketzer', $this->getLidNaam());
		} else {
			$url = $this->getModel()::getUrl();
			return ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($this->content) . ' bestaat niet. <a href="' . $url . 'beheren">Zoeken</a>';
		}
	}

	/**
	 * @return AbstractGroepenModel
	 */
	abstract public function getModel();

	protected function groepLight(AbstractGroep $groep, $tag, $leden) {
		return BbHelper::lightLinkBlock($tag, $groep->getUrl(), $groep->naam, $groep->aantalLeden() . ' ' . $leden);
	}

	abstract public function getLidNaam();

	public function render() {
		$groep = $this->getGroep();
		if (!$groep) {
			$url = $this->getModel()::getUrl();
			throw new BbException(ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($this->content) . ' bestaat niet. <a href="' . $url . '/beheren">Zoeken</a>');
		}
		return $this->groep($groep);
	}

	protected function groep(AbstractGroep $groep) {
		if ($groep->versie == GroepVersie::V2) {
			$uid = LoginModel::getUid();
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

	private function getGroep()
	{
		$this->content = (int)$this->content;
		$groep = $this->getModel()::get($this->content);
		if (!$groep) {
			throw new BbException("Groep met id $this->content does not exist");
		}
		return $groep;
	}
}
