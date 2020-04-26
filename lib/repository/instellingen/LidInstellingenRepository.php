<?php

namespace CsrDelft\repository\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\instellingen\InstellingConfiguration;
use CsrDelft\common\instellingen\InstellingType;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\entity\instellingen\LidInstelling;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;


/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de instellingen bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 * @method LidInstelling|null findOneBy(array $criteria, array $orderBy = null)
 * @method LidInstelling[]    findAll()
 * @method LidInstelling|null find($id, $lockMode = null, $lockVersion = null)
 * @method LidInstelling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LidInstellingenRepository extends AbstractRepository {
	use YamlInstellingen;

	/**
	 * @param ManagerRegistry $registry
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws LoaderLoadException
	 */
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, LidInstelling::class);

		$this->load('instellingen/lid_instelling.yaml', new InstellingConfiguration());
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
		$result = [];
		foreach ($this->findBy(['uid' => $uid]) as $instelling) {
			if (!isset($result[$instelling->module])) {
				$result[$instelling->module] = [];
			}
			$result[$instelling->module][$instelling->instelling_id] = $instelling->waarde;
		}

		return $result;
	}

	public function getValue($module, $id) {
		$instelling = $this->getInstelling($module, $id);

		if ($this->getType($module, $id) == InstellingType::Integer) {
			return (int)$instelling->waarde;
		}

		return $instelling->waarde;
	}

	/**
	 * Haal een instelling op uit het cache of de database.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 *
	 * @param string $module
	 * @param string $id
	 * @param string|null $uid
	 * @return LidInstelling
	 * @throws CsrException indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	protected function getInstelling($module, $id, $uid = null) {
		if (!$uid) {
			$uid = $this->getUid();
		}
		$instelling = $this->findOneBy(['module' => $module, 'instelling_id' => $id, 'uid' => $uid]);
		if ($this->hasKey($module, $id)) {
			if (!$instelling) {
				$instelling = $this->newInstelling($module, $id, $uid);
			}
			return $instelling;
		} else {
			if ($instelling) {
				// Haal niet-bestaande instelling uit de database
				$this->getEntityManager()->remove($instelling);
				$this->getEntityManager()->flush();
			}
			throw new CsrException(sprintf('Instelling bestaat niet: "%s" module: "%s".', $id, $module));
		}
	}

	private function getUid() {
		return LoginModel::getUid();
	}

	protected function newInstelling($module, $id, $uid) {
		$instelling = new LidInstelling();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$instelling->uid = $uid;

		$this->getEntityManager()->persist($instelling);
		$this->getEntityManager()->flush();
		return $instelling;
	}

	public function getDefault($module, $id) {
		return $this->getField($module, $id, InstellingConfiguration::FIELD_DEFAULT);
	}

	public function getType($module, $id) {
		if ($this->hasKey($module, $id)) {
			return $this->getField($module, $id, InstellingConfiguration::FIELD_TYPE);
		} else {
			return null;
		}
	}

	/**
	 * @throws Exception
	 */
	public function saveAll() {
		foreach ($this->getAll() as $module => $instellingen) {
			foreach ($instellingen as $id => $waarde) {
				if ($this->getType($module, $id) === InstellingType::Integer) {
					$filter = FILTER_SANITIZE_NUMBER_INT;
				} else {
					$filter = FILTER_SANITIZE_STRING;
				}
				$waarde = filter_input(INPUT_POST, $module . '_' . $id, $filter);
				if (!$this->isValidValue($module, $id, $waarde)) {
					$waarde = $this->getDefault($module, $id);
				}
				$instelling = new LidInstelling();
				$instelling->module = $module;
				$instelling->instelling_id = $id;
				$instelling->uid = $this->getUid();
				$instelling->waarde = $waarde;
				$this->getEntityManager()->persist($instelling);
			}
		}
		$this->getEntityManager()->flush();
	}

	public function isValidValue($module, $id, $waarde) {
		$options = $this->getTypeOptions($module, $id);
		switch ($this->getType($module, $id)) {
			case InstellingType::Enumeration:
				return isset($options[$waarde]) || in_array($waarde, $options);
			case InstellingType::Integer:
				return is_numeric($waarde) && $waarde >= $options[0] && $waarde <= $options[1];
			case InstellingType::String:
				return strlen($waarde) >= $options[0] && strlen($waarde) <= $options[1] && preg_match('/^[\w\-_\. ]*$/', $waarde);
			default:
				return false;
		}
	}

	public function getTypeOptions($module, $id) {
		return $this->getField($module, $id, InstellingConfiguration::FIELD_OPTIES);
	}

	public function resetForAll($module, $id) {
		$this->createQueryBuilder('i')
			->andWhere('i.module = :module')
			->andWhere('i.instelling_id = :id')
			->setParameters(['module' => $module, 'id' => $id])
			->delete()
			->getQuery()
			->execute();
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
	 * @param LidInstelling $entity
	 * @throws CsrGebruikerException
	 */
	public function update($entity) {
		if (!$this->hasKey($entity->module, $entity->instelling_id)) {
			throw new CsrGebruikerException("Instelling '{$entity->instelling_id}' uit module '{$entity->module}' niet gevonden.");
		}

		$type = $this->getTypeOptions($entity->module, $entity->instelling_id);
		$typeOptions = $this->getTypeOptions($entity->module, $entity->instelling_id);

		if ($type === InstellingType::Enumeration && !in_array($entity->waarde, $typeOptions)) {
			throw new CsrGebruikerException("Waarde is geen geldige optie");
		}

		if ($type === InstellingType::String) {
			if (strlen($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException("Waarde is te lang");
			}

			if (strlen($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException("Waarde is te kort");
			}
		}

		if ($type === InstellingType::Integer) {
			if (intval($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException("Waarde is te lang");
			}

			if (intval($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException("Waarde is te kort");
			}
		}

		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
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
	public function getInstellingVoorLid($module, $id, $uid) {
		return $this->getInstelling($module, $id, $uid)->waarde;
	}

	/**
	 */
	public function opschonen() {
		foreach ($this->findAll() as $instelling) {
			if (!$this->hasKey($instelling->module, $instelling->instelling_id)) {
				$this->getEntityManager()->remove($instelling);
			}
		}

		$this->getEntityManager()->flush();
	}
}
