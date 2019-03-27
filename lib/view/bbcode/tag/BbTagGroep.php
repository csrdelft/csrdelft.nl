<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\security\AccessAction;
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

	abstract public function getLidNaam();

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

	public function parse($arguments = []) {
		if (isset($arguments[$this->getTagName()])) {
			$id = $arguments[$this->getTagName()];
		} else {
			$id = $this->parser->parseArray(['[/' . $this->getTagName() . ']'], []);
		}
		$groep = $this->getModel()::get($id);
		if ($groep) {
			return $this->groep($groep);
		} else {
			$url = $this->getModel()::getUrl();
			return ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($id) . ' bestaat niet. <a href="' . $url . '/beheren">Zoeken</a>';
		}
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
