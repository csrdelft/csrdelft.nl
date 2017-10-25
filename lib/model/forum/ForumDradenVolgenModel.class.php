<?php

namespace CsrDelft\model\forum;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadVolgen;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * ForumDradenVolgenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDradenVolgenModel extends CachedPersistenceModel {

	const ORM = ForumDraadVolgen::class;

	protected function maakForumDraadVolgen($draad_id) {
		$volgen = new ForumDraadVolgen();
		$volgen->draad_id = $draad_id;
		$volgen->uid = LoginModel::getUid();
		$this->create($volgen);
		return $volgen;
	}

	public function getAantalVolgenVoorLid() {
		return $this->count('uid = ?', array(LoginModel::getUid()));
	}

	public function getVolgersVanDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ?', array($draad->draad_id));
	}

	public function getVolgenVoorLid(ForumDraad $draad) {
		return $this->existsByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	public function setVolgenVoorLid(ForumDraad $draad, $volgen = true) {
		if ($volgen) {
			if (!$this->getVolgenVoorLid($draad)) {
				$this->maakForumDraadVolgen($draad->draad_id);
			}
		} elseif ($this->getVolgenVoorLid($draad)) {
			$rowCount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
			if ($rowCount !== 1) {
				throw new CsrException('Volgen stoppen mislukt');
			}
		}
	}

	public function volgNietsVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $volgen) {
			$this->delete($volgen);
		}
	}

	public function stopVolgenVoorIedereen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $volgen) {
			$this->delete($volgen);
		}
	}

}
