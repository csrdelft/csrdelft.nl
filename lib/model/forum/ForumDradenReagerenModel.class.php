<?php

namespace CsrDelft\model\forum;

use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadReageren;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;

/**
 * ForumDradenReagerenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDradenReagerenModel extends PersistenceModel {

	const ORM = ForumDraadReageren::class;

	protected function maakForumDraadReageren(ForumDeel $deel, $draad_id = null, $concept = null, $titel = null) {
		$reageren = new ForumDraadReageren();
		$reageren->forum_id = $deel->forum_id;
		$reageren->draad_id = (int)$draad_id;
		$reageren->uid = LoginModel::getUid();
		$reageren->datum_tijd = getDateTime();
		$reageren->concept = $concept;
		$reageren->titel = $titel;
		$this->create($reageren);
		return $reageren;
	}

	/**
	 * Fetch reageren object voor deel of draad.
	 *
	 * @param ForumDeel $deel
	 * @param int $draad_id
	 * @return ForumDraadReageren
	 */
	protected function getReagerenDoorLid(ForumDeel $deel, $draad_id = null) {
		return $this->retrieveByPrimaryKey(array($deel->forum_id, (int)$draad_id, LoginModel::getUid()));
	}

	public function getReagerenVoorDraad(ForumDraad $draad) {
		return $this->find('draad_id = ? AND uid != ? AND datum_tijd > ?', array($draad->draad_id, LoginModel::getUid(), getDateTime(strtotime(instelling('forum', 'reageren_tijd')))));
	}

	public function getReagerenVoorDeel(ForumDeel $deel) {
		return $this->find('forum_id = ? AND draad_id = 0 AND uid != ? AND datum_tijd > ?', array($deel->forum_id, LoginModel::getUid(), getDateTime(strtotime(instelling('forum', 'reageren_tijd')))));
	}

	public function verwijderLegeConcepten() {
		foreach ($this->find('concept IS NULL AND datum_tijd < ?', array(getDateTime(strtotime(instelling('forum', 'reageren_tijd'))))) as $reageren) {
			$this->delete($reageren);
		}
	}

	public function verwijderReagerenVoorDraad(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $reageren) {
			$this->delete($reageren);
		}
	}

	public function verwijderReagerenVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $reageren) {
			$this->delete($reageren);
		}
	}

	public function setWanneerReagerenDoorLid(ForumDeel $deel, $draad_id = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			$reageren->datum_tijd = getDateTime();
			$this->update($reageren);
		} else {
			$this->maakForumDraadReageren($deel, $draad_id);
		}
	}

	public function getConcept(ForumDeel $deel, $draad_id = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if ($reageren) {
			return $reageren->concept;
		}
		return null;
	}

	public function getConceptTitel(ForumDeel $deel) {
		$reageren = $this->getReagerenDoorLid($deel);
		if ($reageren) {
			return $reageren->titel;
		}
		return null;
	}

	public function setConcept(ForumDeel $deel, $draad_id = null, $concept = null, $titel = null) {
		$reageren = $this->getReagerenDoorLid($deel, $draad_id);
		if (empty($concept)) {
			if ($reageren) {
				$this->delete($reageren);
			}
		} elseif ($reageren) {
			$reageren->concept = $concept;
			$reageren->titel = $titel;
			$this->update($reageren);
		} else {
			$this->maakForumDraadReageren($deel, $draad_id, $concept, $titel);
		}
	}

}
