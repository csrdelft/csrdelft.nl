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
use CsrDelft\Orm\DynamicEntityModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Persistence\Database;
use PDO;

/**
 * AbstractGroepenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class AbstractGroepenModel extends CachedPersistenceModel {

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'begin_moment DESC';

	public static function get($id) {
		if (is_numeric($id)) {
			return static::instance()->retrieveByPrimaryKey(array($id));
		}
		$groepen = static::instance()->prefetch('familie = ? AND status = ?', array($id, GroepStatus::HT), null, null, 1);
		return reset($groepen);
	}

	public static function getNaam() {
		return strtolower(str_replace('Model', '', classNameZonderNamespace(get_called_class())));
	}

	public static function getUrl() {
		return '/groepen/' . static::getNaam() . '/';
	}

	private static $old;

	/**
	 * Oude groep-id's omnummeren. 'snaam' mag ook.
	 *
	 * @param int|string $id
	 * @return boolean
	 */
	public static function omnummeren($id) {
		if (!isset(self::$old)) {
			self::$old = DynamicEntityModel::makeModel('groep');
		}
		$groep = self::$old->find('id = ? OR (snaam = ? AND status = ?)', array($id, $id, 'ht'), null, null, 1)->fetch();
		if (!$groep) {
			setMelding('Groep niet gevonden: ' . htmlspecialchars($id), -1);
			return false;
		}
		$model = $groep->model;
		$namespacedModel = 'CsrDelft\\model\\groepen\\' . $model;
		if (!class_exists($namespacedModel)) {
			setMelding('Model niet gevonden: ' . $model, -1);
			return false;
		}
		return $namespacedModel::get($groep->omnummering);
	}

	/**
	 * Groepen waarvan de gevraagde gebruiker de wikipagina's mag lezen en bewerken.
	 *
	 * @param string $uid
	 * @return array
	 */
	public static function getWikiToegang($uid) {
		$result = array();
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			return $result;
		}
		if ($profiel->isLid() OR $profiel->isOudlid()) {
			$result[] = 'htleden-oudleden';
		}
		// 1 generatie vooruit en 1 achteruit (default order by)
		$ft = BesturenModel::instance()->find('status = ?', array(GroepStatus::FT), null, null, 1)->fetch();
		$ht = BesturenModel::instance()->find('status = ?', array(GroepStatus::HT), null, null, 1)->fetch();
		$ot = BesturenModel::instance()->find('status = ?', array(GroepStatus::OT), null, null, 1)->fetch();
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

	public function nieuw() {
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
	 * @param PersistentEntity $groep
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
	 * @return boolean
	 */
	public function converteer(AbstractGroep $oldgroep, AbstractGroepenModel $oldmodel, $soort = null) {
		// groep converteren
		try {
			$newgroep = $this->nieuw($soort);
			foreach ($oldgroep->getValues() as $attribute => $value) {
				if (property_exists($newgroep, $attribute)) {
					$newgroep->$attribute = $value;
				}
			}
			$newgroep->id = null;
			$this->create($newgroep);
		} catch (\Exception $e) {
			setMelding('Converteren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// leden converteren
		try {
			$leden = $newgroep::leden;
			$ledenmodel = $leden::instance();
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
		} catch (\Exception $e) {
			setMelding('Leden converteren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// omnummeren
		try {
			if (!isset(self::$old)) {
				self::$old = DynamicEntityModel::makeModel('groep');
			}
			$omnummering = self::$old->find('omnummering = ? AND model = ?', array($oldgroep->id, classNameZonderNamespace(get_class($oldmodel))), null, null, 1)->fetch();
			if ($omnummering) {
				$omnummering->omnummering = $newgroep->id;
				$omnummering->model = get_class($this);
				self::$old->update($omnummering);
			}
		} catch (\Exception $ex) {
			setMelding('Omnummeren mislukt: ' . $ex->getMessage(), -1);
			return false;
		}
		// leden verwijderen
		try {
			$oldleden = $oldgroep::leden;
			$oldledenmodel = $oldleden::instance();
			foreach ($oldgroep->getLeden() as $oldlid) {
				$oldledenmodel->delete($oldlid);
			}
		} catch (\Exception $ex) {
			setMelding('Leden verwijderen mislukt: ' . $ex->getMessage(), -1);
			return false;
		}
		// groep verwijderen
		try {
			$oldmodel->delete($oldgroep);
		} catch (\Exception $ex) {
			setMelding('Groep verwijderen mislukt: ' . $ex->getMessage(), -1);
			return false;
		}
		return $newgroep;
	}

	/**
	 * Return groepen by GroepStatus voor lid.
	 *
	 * @param string $uid
	 * @param GroepStatus|array $status
	 * @return AbstractGroep[]
	 */
	public function getGroepenVoorLid($uid, $status = null) {
		$orm = static::ORM;
		$leden = $orm::leden;
		$ids = Database::instance()->sqlSelect(array('DISTINCT groep_id'), $leden::instance()->getTableName(), 'uid = ?', array($uid))->fetchAll(PDO::FETCH_COLUMN);
		if (empty($ids)) {
			return array();
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
