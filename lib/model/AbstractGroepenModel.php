<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Exception;
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
	 * @var AccessModel
	 */
	private $accessModel;

	/**
	 * AbstractGroepenModel constructor.
	 * @param AccessModel $accessModel
	 */
	public function __construct(AccessModel $accessModel) {
		parent::__construct();

		$this->accessModel = $accessModel;
	}

	/**
	 * @param $id
	 * @return AbstractGroep|false
	 */
	public function get($id) {
		if (is_numeric($id)) {
			return $this->retrieveByPrimaryKey([$id]);
		}
		$groepen = $this->prefetch('familie = ? AND status = ?', [$id, GroepStatus::HT], null, null, 1);
		return reset($groepen);
	}

	public static function getNaam() {
		return strtolower(str_replace('Model', '', classNameZonderNamespace(get_called_class())));
	}

	public static function getUrl() {
		return '/groepen/' . static::getNaam();
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
		$this->accessModel->setAcl(static::ORM, reset($primary_key_values), array());
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
			return $this->database->_transaction(function () use ($oldgroep, $oldmodel, $soort) {
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
		} catch (Exception $ex) {
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
		$ids = $this->database->sqlSelect(['DISTINCT groep_id'], $orm::getLedenModel()->getTableName(), 'uid = ?', [$uid])->fetchAll(PDO::FETCH_COLUMN);
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
