<?php

namespace CsrDelft\repository\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\entity\Instelling;
use CsrDelft\model\instellingen\InstellingConfiguration;
use CsrDelft\model\OrmTrait;
use CsrDelft\Orm\Entity\PersistentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;

/**
 * InstellingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method Instelling[]    ormFind($criteria = null, $criteria_params = [], $group_by = null, $order_by = null, $limit = null, $start = 0)
 * @method Instelling|null doctrineFind($id, $lockMode = null, $lockVersion = null)
 * @method Instelling|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instelling[]    findAll()
 * @method Instelling|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instelling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstellingenRepository extends ServiceEntityRepository {
	use OrmTrait;
	use YamlInstellingen;

	const ORM = Instelling::class;

	/**
	 * InstellingenModel constructor.
	 * @param ManagerRegistry $manager
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws FileLoaderLoadException
	 */
	public function __construct(ManagerRegistry $manager) {
		parent::__construct($manager, Instelling::class);

		$this->load('instellingen/stek_instelling.yaml', new InstellingConfiguration());
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
		if ($this->hasKey($module, $id) && $this->find(['module' => $module, 'instelling_id' => $id]) != null) {
			return $this->find(['module' => $module, 'instelling_id' => $id]);
		} else if ($this->hasKey($module, $id)) {
			return $this->newInstelling($module, $id);
		} else {
			$entity = $this->find(['module' => $module, 'instelling_id' => $id]);
			if ($entity != null) {
				$this->delete($entity);
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
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getDefault($module, $id) {
		return $this->getField($module, $id, InstellingConfiguration::FIELD_DEFAULT);
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
		foreach ($this->findAll() as $instelling) {
			if (!$this->hasKey($instelling->module, $instelling->instelling_id)) {
				$this->delete($instelling);
			}
		}
	}
}
