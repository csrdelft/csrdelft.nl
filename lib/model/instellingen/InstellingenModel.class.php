<?php

namespace CsrDelft\model\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\model\entity\Instelling;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;

/**
 * InstellingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class InstellingenModel extends CachedPersistenceModel {
	use YamlInstellingen;

	const ORM = Instelling::class;

	/**
	 * Store instellingen array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

	/**
	 * InstellingenModel constructor.
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws FileLoaderLoadException
	 */
	protected function __construct() {
		parent::__construct();

		$this->load('instellingen/stek_instelling.yaml', new InstellingConfiguration());
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public static function getDefinition($module, $id) {
		return static::instance()->getValue($module, $id);
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getValue($module, $id) {
		return $this->getInstelling($module, $id)->waarde;
	}

	/**
	 * Haal een instelling op uit het cache of de database.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 *
	 * @param string $module
	 * @param string $id
	 * @return Instelling|PersistentEntity
	 * @throws CsrException indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	protected function getInstelling($module, $id) {
		if ($this->hasKey($module, $id) && $this->existsByPrimaryKey([$module, $id])) {
			return $this->retrieveByPrimaryKey([$module, $id]);
		} else if ($this->hasKey($module, $id)) {
			return $this->newInstelling($module, $id);
		} else {
			if ($this->existsByPrimaryKey([$module, $id])) {
				$this->deleteByPrimaryKey([$module, $id]);
			}
			throw new CsrException(sprintf('Instelling bestaat niet: "%s" module: "%s".', $id, $module));
		}
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return Instelling
	 */
	protected function newInstelling($module, $id) {
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$this->create($instelling);
		return $instelling;
	}

	/**
	 * @return string[][]
	 */
	public function getInstellingen() {
		$instellingen = array();
		foreach ($this->getModules() as $module) {
			$instellingen[$module] = $this->getModuleKeys($module);
		}
		return $instellingen;
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getDefault($module, $id) {
		return $this->getField($module, $id, 'default');
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @param string $waarde
	 *
	 * @return Instelling
	 */
	public function wijzigInstelling($module, $id, $waarde) {
		$instelling = $this->getInstelling($module, $id);
		$instelling->waarde = $waarde;
		$this->update($instelling);
		return $instelling;
	}

	/**
	 */
	public function opschonen() {
		foreach ($this->find() as $instelling) {
			if (!static::has($instelling->module, $instelling->instelling_id)) {
				$this->delete($instelling);
			}
		}
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return bool
	 */
	public static function has($module, $id) {
		return static::instance()->hasKey($module, $id);
	}

}
