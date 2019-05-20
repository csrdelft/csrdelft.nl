<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\view\ledenmemory\LedenMemoryScoreTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbLedenmemoryscores extends BbTag {

	public function getTagName() {
		return 'ledenmemoryscores';
	}

	public function parseLight($arguments = []) {
		list($groep, $titel) = $this->getGroepAndTitel($arguments);
		return $this->lightLinkBlock('ledenmemoryscores', '/forum/onderwerp/8017', 'Ledenmemory Scores', $titel);
	}

	/**
	 * @param $arguments
	 * @return array
	 */
	private function getGroepAndTitel($arguments): array {
		LedenMemoryScoresModel::instance();
		$groep = null;
		$titel = null;
		if (isset($arguments['verticale'])) {
			$v = filter_var($arguments['verticale'], FILTER_SANITIZE_STRING);
			if (strlen($v) > 1) {
				$verticale = VerticalenModel::instance()->find('naam LIKE ?', array('%' . $v . '%'), null, null, 1)->fetch();
			} else {
				$verticale = VerticalenModel::get($v);
			}
			if ($verticale) {
				$titel = ' Verticale ' . $verticale->naam;
				$groep = $verticale;
			}
		} elseif (isset($arguments['lichting'])) {
			$l = (int)filter_var($arguments['lichting'], FILTER_SANITIZE_NUMBER_INT);
			if ($l < 1950) {
				$l = LichtingenModel::getJongsteLidjaar();
			}
			$lichting = LichtingenModel::get($l);
			if ($lichting) {
				$titel = ' Lichting ' . $lichting->lidjaar;
				$groep = $lichting;
			}
		}
		return array($groep, $titel);
	}

	public function parse($arguments = []) {
		list($groep, $titel) = $this->getGroepAndTitel($arguments);
		$table = new LedenMemoryScoreTable($groep, $titel);
		return $table->getHtml();
	}
}
