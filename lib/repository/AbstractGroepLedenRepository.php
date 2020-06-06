<?php

namespace CsrDelft\repository;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\AbstractGroepLid;
use CsrDelft\service\security\LoginService;

/**
 * AbstractGroepLedenModel.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method AbstractGroepLid|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractGroepLid|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractGroepLid[]    findAll()
 * @method AbstractGroepLid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractGroepLedenRepository extends AbstractRepository {
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'lid_sinds ASC';

	/**
	 * @param AbstractGroep $groep
	 * @param $uid
	 *
	 * @return AbstractGroepLid|null
	 */
	public function get(AbstractGroep $groep, $uid) {
		return $this->find(['groep_id' => $groep->id, 'uid' => $uid]);
	}

	/**
	 * @param AbstractGroep $groep
	 * @param $uid
	 *
	 * @return AbstractGroepLid
	 */
	public function nieuw(AbstractGroep $groep, $uid) {
		$orm = $this->_entityName;
		/** @var AbstractGroepLid $lid */
		$lid = new $orm();
		$lid->groep = $groep;
		$lid->groep_id = $groep->id;
		$lid->uid = $uid;
		$lid->profiel = $uid ? ProfielRepository::get($uid) : null;
		$lid->door_uid = LoginService::getUid();
		$lid->door_profiel = LoginService::getProfiel();
		$lid->lid_sinds = date_create_immutable();
		$lid->opmerking = null;
		return $lid;
	}
}
