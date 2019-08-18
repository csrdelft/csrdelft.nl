<?php

namespace CsrDelft\model\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\model\entity\instellingen\LidInstelling;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\Orm\Persistence\Database;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;


/**
 * LidInstellingenModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de instellingen bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 */
class LidInstellingenModel extends CachedPersistenceModel {
	use YamlInstellingen;

	const ORM = LidInstelling::class;
	protected $memcache_prefetch = true;
	/**
	 * InstellingenModel constructor.
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws FileLoaderLoadException
	 */
	protected function __construct() {
		parent::__construct();

		$this->load('instellingen/lid_instelling.yaml', new InstellingConfiguration());
	}

	/**
	 * Uid van lid waarvoor instellingen opgehaald moeten worden.
	 * Indien niet ingevuld wordt huidig lid gebruikt.
	 * @var int
	 */
	private $uid;

	private function getUid() {
		return $this->uid ? $this->uid : LoginModel::getUid();
	}
	/**
	 * Geeft een array terug van dezelfde vorm als de instellingen, maar gevuld met gekozen instellingen.
	 *
	 * Let op, kan minder bevatten dan de instellingen array.
	 *
	 * @param string $uid
	 * @return string[]
	 */
	public function getAllForLid(string $uid) {
		return array_reduce($this->find('uid = ?', [$uid])->fetchAll(), function ($carry, LidInstelling $instelling) {
			if (!isset($carry[$instelling->module])) $carry[$instelling->module] = [];

			$carry[$instelling->module][$instelling->instelling_id] = $instelling->waarde;

			return $carry;
		}, []);
	}

	/**
	 * Haal een instelling op uit het cache of de database.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 *
	 * @param string $module
	 * @param string $id
	 * @return LidInstelling
	 * @throws CsrException indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	protected function getInstelling($module, $id) {
		$instelling = $this->retrieveByPrimaryKey([$module, $id, $this->getUid()]);
		if ($this->hasKey($module, $id)) {
			if (!$instelling) {
				$instelling = $this->newInstelling($module, $id);
			}
			return $instelling;
		} else {
			if ($instelling) {
				// Haal niet-bestaande instelling uit de database
				$this->delete($instelling);
			}
			throw new CsrException(sprintf('Instelling bestaat niet: "%s" module: "%s".', $id, $module));
		}
	}

	protected function newInstelling($module, $id) {
		$instelling = new LidInstelling();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$instelling->uid = $this->getUid();
		$this->create($instelling);
		return $instelling;
	}

	public function getType($module, $id) {
		if ($this->hasKey($module, $id)) {
			return $this->getField($module, $id, InstellingConfiguration::FIELD_TYPE);
		} else {
			return null;
		}
	}

	public function getTypeOptions($module, $id) {
		return $this->getField($module, $id, InstellingConfiguration::FIELD_OPTIES);
	}

	public function getDefault($module, $id) {
		return $this->getField($module, $id, InstellingConfiguration::FIELD_DEFAULT);
	}

	public function isValidValue($module, $id, $waarde) {
		$options = $this->getTypeOptions($module, $id);
		switch ($this->getType($module, $id)) {
			case InstellingType::Enumeration:
				if (isset($options[$waarde]) || in_array($waarde, $options)) {
					return true;
				}
				break;

			case InstellingType::Integer:
				if ($waarde >= $options[0] AND $waarde <= $options[1]) {
					return true;
				}
				break;

			case InstellingType::String:
				if (strlen($waarde) >= $options[0] AND strlen($waarde) <= $options[1] AND preg_match('/^[\w\-_\. ]*$/', $waarde)) {
					return true;
				}
				break;
		}
		return false;
	}

	public function getValue($module, $id) {
		return $this->getInstelling($module, $id)->waarde;
	}

	/**
	 * @throws \Exception
	 */
	public function save() {
		// create matrix for sqlInsertMultiple
		$properties[] = $this->getAttributes();
		foreach ($this->getAll() as $module => $instellingen) {
			foreach ($instellingen as $id => $waarde) {
				if ($this->getType($module, $id) === T::Integer) {
					$filter = FILTER_SANITIZE_NUMBER_INT;
				} else {
					$filter = FILTER_SANITIZE_STRING;
				}
				$waarde = filter_input(INPUT_POST, $module . '_' . $id, $filter);
				if (!$this->isValidValue($module, $id, $waarde)) {
					$waarde = $this->getDefault($module, $id);
				}
				$properties[] = array($module, $id, $waarde, $this->getUid());
			}
		}
		Database::instance()->sqlInsertMultiple($this->getTableName(), $properties, true);
		$this->flushCache(true);
	}

	public function resetForAll($module, $id) {
		Database::instance()->sqlDelete($this->getTableName(), 'module = ? AND instelling_id = ?', array($module, $id));
		$this->flushCache(true);
	}

	/**
	 * @param LidInstelling|PersistentEntity $entity
	 * @return int
	 * @throws CsrGebruikerException
	 */
	public function update(PersistentEntity $entity) {
		if (!$this->hasKey($entity->module, $entity->instelling_id)) {
			throw new CsrGebruikerException("Instelling '{$entity->instelling_id}' uit module '{$entity->module}' niet gevonden.");
		}

		$type = $this->getTypeOptions($entity->module, $entity->instelling_id);
		$typeOptions = $this->getTypeOptions($entity->module, $entity->instelling_id);

		if ($type === T::Enumeration
			&& !in_array($entity->waarde, $typeOptions)) {
			throw new CsrGebruikerException("Waarde is geen geldige optie");
		}

		if ($type === T::String) {
			if (strlen($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException("Waarde is te lang");
			}

			if (strlen($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException("Waarde is te kort");
			}
		}

		if ($type === T::Integer) {
			if (intval($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException("Waarde is te lang");
			}

			if (intval($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException("Waarde is te kort");
			}
		}

		return parent::update($entity);
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @param string $waarde
	 *
	 * @return LidInstelling
	 */
	public function wijzigInstelling($module, $id, $waarde) {
		$instelling = $this->getInstelling($module, $id);
		$instelling->waarde = $waarde;
		$this->update($instelling);
		return $instelling;
	}

	/**
	 * Haal een instelling op uit het cache of de database voor opgegeven lid.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 *
	 * @param string $module
	 * @param string $id
	 * @param int $uid
	 * @return string
	 */
	public static function getInstellingVoorLid($module, $id, $uid) {
		$instellingen = static::instance();
		$instellingen->uid = $uid;
		return $instellingen->getInstelling($module, $id)->waarde;
	}

	/**
	 */
	public function opschonen() {
		foreach ($this->find() as $instelling) {
			if (!$this->hasKey($instelling->module, $instelling->instelling_id)) {
				$this->delete($instelling);
			}
		}
	}
}
