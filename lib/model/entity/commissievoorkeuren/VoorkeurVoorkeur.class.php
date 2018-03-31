<?php

namespace CsrDelft\model\entity\commissievoorkeuren;

use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccessModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class VoorkeurVoorkeur extends PersistentEntity {
	/**
	 * @var string
	 */
	public $uid;

	/**
	 * @var int
	 */
	public $cid;

	/**
	 * @var int
	 */
	public $voorkeur;

	/**
	 * @var DateTime
	 */
	public $timestamp;

	/**
	 * @var string
	 */
	protected static $table_name = 'voorkeurVoorkeur';

	/**
	 * @return Profiel
	 */
	public function getProfiel() {
		return ProfielModel::instance()->retrieveByUUID($this->uid);
	}

	/**
	 * @return VoorkeurCommissie
	 */
	public function getCommissie() {
		return VoorkeurCommissieModel::instance()->retrieveByUUID($this->cid);
	}

	public function heeftGedaan() {
		$commissie = $this->getCommissie();
		return AccessModel::mag($this->getProfiel()->getAccount(), 'commissie:' . $commissie->naam . ',commissie:' . $commissie->naam . ':ot');
	}

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'uid' => [T::UID, false],
		'cid' => [T::Integer, false],
		'voorkeur' => [T::Integer, false],
		'timestamp' => [T::Timestamp, false, "on update CURRENT_TIMESTAMP"]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['uid', 'cid'];
}
