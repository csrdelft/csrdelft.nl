<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
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
		// Controleer rechten
		if (!$groep->mag(AccessAction::Bekijken)) {
			return '';
		}
		$view = new GroepView($groep, null, false, true);
		return $view->getHtml();
	}
}
