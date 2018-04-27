<?php

namespace CsrDelft\model\forum;

use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadGelezen;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * ForumDradenGelezenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class ForumDradenGelezenModel extends CachedPersistenceModel {

	const ORM = ForumDraadGelezen::class;

	protected function maakForumDraadGelezen($draad_id) {
		$gelezen = new ForumDraadGelezen();
		$gelezen->draad_id = $draad_id;
		$gelezen->uid = LoginModel::getUid();
		$gelezen->datum_tijd = getDateTime();
		$this->create($gelezen);
		return $gelezen;
	}

	public function getWanneerGelezenDoorLid(ForumDraad $draad) {
		return $this->retrieveByPrimaryKey(array($draad->draad_id, LoginModel::getUid()));
	}

	/**
	 * Ga na welke posts op de huidige pagina het laatst is geplaatst of gewijzigd.
	 *
	 * @param ForumDraad $draad
	 * @param int $timestamp
	 * @return int number of rows affected
	 */
	public function setWanneerGelezenDoorLid(ForumDraad $draad, $timestamp = null) {
		$gelezen = $this->getWanneerGelezenDoorLid($draad);
		if (!$gelezen) {
			$gelezen = $this->maakForumDraadGelezen($draad->draad_id);
		}
		if (is_int($timestamp)) {
			$gelezen->datum_tijd = getDateTime($timestamp);
		} else {
			foreach ($draad->getForumPosts() as $post) {
				if (strtotime($post->laatst_gewijzigd) > strtotime($gelezen->datum_tijd)) {
					$gelezen->datum_tijd = $post->laatst_gewijzigd;
				}
			}
		}
		return $this->update($gelezen);
	}

	public function getLezersVanDraad(ForumDraad $draad) {
		return $this->prefetch('draad_id = ?', array($draad->draad_id));
	}

	public function verwijderDraadGelezen(ForumDraad $draad) {
		foreach ($this->find('draad_id = ?', array($draad->draad_id)) as $gelezen) {
			$this->delete($gelezen);
		}
	}

	public function verwijderDraadGelezenVoorLid($uid) {
		foreach ($this->find('uid = ?', array($uid)) as $gelezen) {
			$this->delete($gelezen);
		}
	}

}
