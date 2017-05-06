<?php
use CsrDelft\Orm\CachedPersistenceModel;

require_once 'forum/model/entity/ForumDraadVerbergen.class.php';

/**
 * ForumDradenVerbergenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDradenVerbergenModel extends CachedPersistenceModel {

	const ORM = ForumDraadVerbergen::class;
	const DIR = 'forum/';

	protected static $instance;

	protected function maakForumDraadVerbergen($draad_id) {
		$verbergen = new ForumDraadVerbergen();
		$verbergen->draad_id = $draad_id;
		$verbergen->uid = LoginModel::getUid();
		$this->create($verbergen);
		return $verbergen;
	}

	public function getAantalVerborgenVoorLid() {
		return $this->count('uid = ?', array(LoginModel::getUid()));
	}

	public function getVerbergenVoorLid(ForumDraad $draad) {
		return $this->existsByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	public function setVerbergenVoorLid(ForumDraad $draad, $verbergen = true) {
		if ($verbergen) {
			if (!$this->getVerbergenVoorLid($draad)) {
				$this->maakForumDraadVerbergen($draad->draad_id);
			}
		} elseif ($this->getVerbergenVoorLid($draad)) {
			$rowCount = $this->deleteByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
			if ($rowCount !== 1) {
				throw new Exception('Weer tonen mislukt');
			}
		}
	}

	public function toonAllesVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $verborgen) {
			$this->delete($verborgen);
		}
	}

	public function toonDraadVoorIedereen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $verborgen) {
			$this->delete($verborgen);
		}
	}

}