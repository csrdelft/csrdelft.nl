<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\leden\CommissieLedenModel;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use PDO;

/**
 * AbstractGroepenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
abstract class AbstractGroepenModel extends CachedPersistenceModel {

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'begin_moment DESC';

	/**
	 * @param $id
	 * @return AbstractGroep|false
	 */
	public static function get($id) {
		if (is_numeric($id)) {
			return static::instance()->retrieveByPrimaryKey([$id]);
		}
		$groepen = static::instance()->prefetch('familie = ? AND status = ?', [$id, GroepStatus::HT], null, null, 1);
		return reset($groepen);
	}

	public static function getNaam() {
		return strtolower(str_replace('Model', '', classNameZonderNamespace(get_called_class())));
	}

	public static function getUrl() {
		return '/groepen/' . static::getNaam() . '/';
	}

	/**
	 * Groepen waarvan de gevraagde gebruiker de wikipagina's mag lezen en bewerken.
	 *
	 * @param string $uid
	 * @return array
	 */
	public static function getWikiToegang($uid) {
		$result = [];
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			return $result;
		}
		if ($profiel->isLid() OR $profiel->isOudlid()) {
			$result[] = 'htleden-oudleden';
		}
		// 1 generatie vooruit en 1 achteruit (default order by)
		$ft = BesturenModel::instance()->find('status = ?', [GroepStatus::FT], null, null, 1)->fetch();
		$ht = BesturenModel::instance()->find('status = ?', [GroepStatus::HT], null, null, 1)->fetch();
		$ot = BesturenModel::instance()->find('status = ?', [GroepStatus::OT], null, null, 1)->fetch();
		if (($ft AND $ft->getLid($uid)) OR ($ht AND $ht->getLid($uid)) OR ($ot AND $ot->getLid($uid))) {
			$result[] = 'bestuur';
		}
		foreach (CommissieLedenModel::instance()->prefetch('uid = ?', array($uid)) as $commissielid) {
			$commissie = CommissiesModel::get($commissielid->groep_id);
			if ($commissie->status === GroepStatus::HT OR $commissie->status === GroepStatus::FT) {
				$result[] = $commissie->familie;
			}
		}
		return $result;
	}

	/**
	 * @param null $soort
	 * @return AbstractGroep
	 */
	public function nieuw(/* @noinspection PhpUnusedParameterInspection */$soort = null) {
		$orm = static::ORM;
		$groep = new $orm();
		$groep->naam = null;
		$groep->familie = null;
		$groep->status = GroepStatus::HT;
		$groep->samenvatting = '';
		$groep->omschrijving = null;
		$groep->begin_moment = null;
		$groep->eind_moment = null;
		$groep->website = null;
		$groep->maker_uid = LoginModel::getUid();
		return $groep;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity|AbstractGroep $groep
	 * @return void
	 */
	public function create(PersistentEntity $groep) {
		$groep->id = (int)parent::create($groep);
	}

	/**
	 * Delete ACL.
	 *
	 * @param array $primary_key_values
	 * @return int number of rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		AccessModel::instance()->setAcl(static::ORM, reset($primary_key_values), array());
		return parent::deleteByPrimaryKey($primary_key_values);
	}

	/**
	 * Converteer groep inclusief leden van klasse.
	 *
	 * @param AbstractGroep $oldgroep
	 * @param AbstractGroepenModel $oldmodel
	 * @param string $soort
	 * @return AbstractGroep|bool
	 */
	public function converteer(AbstractGroep $oldgroep, AbstractGroepenModel $oldmodel, $soort = null) {
		try {
			return Database::transaction(function () use ($oldgroep, $oldmodel, $soort) {
				// groep converteren
				$newgroep = $this->nieuw($soort);
				foreach ($oldgroep->getValues() as $attribute => $value) {
					if (property_exists($newgroep, $attribute)) {
						$newgroep->$attribute = $value;
					}
				}
				$newgroep->id = null;
				$this->create($newgroep);

				// leden converteren
				$ledenmodel = $newgroep::getLedenModel();
				foreach ($oldgroep->getLeden() as $oldlid) {
					$newlid = $ledenmodel->nieuw($newgroep, $oldlid->uid);
					foreach ($oldlid->getValues() as $attribute => $value) {
						if (property_exists($newlid, $attribute)) {
							$newlid->$attribute = $value;
						}
					}
					$newlid->groep_id = $newgroep->id;
					$ledenmodel->create($newlid);
				}

				// leden verwijderen
				$oldledenmodel = $oldgroep::getLedenModel();
				foreach ($oldgroep->getLeden() as $oldlid) {
					$oldledenmodel->delete($oldlid);
				}

				// groep verwijderen
				$oldmodel->delete($oldgroep);
				return $newgroep;
			});
		} catch (\Exception $ex) {
			setMelding($ex->getMessage(), -1);
			return false;
		}
	}

	/**
	 * Return groepen by GroepStatus voor lid.
	 *
	 * @param string $uid
	 * @param GroepStatus|array $status
	 * @return AbstractGroep[]
	 */
	public function getGroepenVoorLid($uid, $status = null) {
		/** @var AbstractGroep $orm */
		$orm = static::ORM;
		$ids = Database::instance()->sqlSelect(['DISTINCT groep_id'], $orm::getLedenModel()->getTableName(), 'uid = ?', [$uid])->fetchAll(PDO::FETCH_COLUMN);
		if (empty($ids)) {
			return [];
		}
		$where = 'id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')';
		if ($status === null) {
			return $this->prefetch($where, $ids);
		} elseif (is_array($status)) {
			$where .= ' AND status IN (' . implode(', ', array_fill(0, count($status), '?')) . ')';
			return $this->prefetch($where, array_merge($ids, $status));
		}
		$where .= ' AND status = ?';
		$ids[] = $status;
		return $this->prefetch($where, $ids);
	}

}
