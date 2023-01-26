<?php

namespace CsrDelft\repository\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\instellingen\InstellingConfiguration;
use CsrDelft\common\instellingen\InstellingType;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\entity\instellingen\LidInstelling;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Contracts\Cache\CacheInterface;

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
class LidInstellingenRepository extends AbstractRepository
{
	use YamlInstellingen;

	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var CacheInterface
	 */
	private $cache;

	/**
	 * @param ManagerRegistry $registry
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws LoaderLoadException
	 */
	public function __construct(
		ManagerRegistry $registry,
		LoginService $loginService,
		CacheInterface $cache
	) {
		parent::__construct($registry, LidInstelling::class);

		$this->load(
			'instellingen/lid_instelling.yaml',
			new InstellingConfiguration()
		);
		$this->loginService = $loginService;
		$this->cache = $cache;
	}

	/**
	 * Geeft een array terug van dezelfde vorm als de instellingen, maar gevuld met gekozen instellingen.
	 *
	 * Let op, kan minder bevatten dan de instellingen array.
	 *
	 * @param string $uid
	 * @return string[]
	 */
	public function getAllForLid(string $uid)
	{
		$result = [];
		foreach ($this->findBy(['profiel' => $uid]) as $instelling) {
			if (!isset($result[$instelling->module])) {
				$result[$instelling->module] = [];
			}
			$result[$instelling->module][$instelling->instelling] =
				$instelling->waarde;
		}

		return $result;
	}

	public function getValue($module, $id)
	{
		$instelling = $this->getInstelling($module, $id);

		if ($this->getType($module, $id) == InstellingType::Integer) {
			return (int) $instelling->waarde;
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
	 * @throws InvalidArgumentException
	 */
	protected function getInstelling($module, $id, $uid = null)
	{
		if (!$uid) {
			$uid = $this->getUid();
		}

		return $this->cache->get(
			$this->getCacheKey($module, $id, $uid),
			function () use ($module, $id, $uid) {
				/** @var LidInstelling $instelling */
				$instelling = $this->findOneBy([
					'module' => $module,
					'instelling' => $id,
					'profiel' => $uid,
				]);

				if ($this->hasKey($module, $id)) {
					if (!$instelling) {
						$instelling = $this->newInstelling($module, $id, $uid);
					}
					return $instelling;
				} else {
					if ($instelling) {
						// Haal niet-bestaande instelling uit de database
						$this->_em->remove($instelling);
						$this->_em->flush();
					}
					throw new CsrException(
						sprintf('Instelling bestaat niet: "%s" module: "%s".', $id, $module)
					);
				}
			}
		);
	}

	private function getUid()
	{
		return $this->loginService->_getUid();
	}

	protected function newInstelling($module, $id, $uid)
	{
		$instelling = new LidInstelling();
		$instelling->module = $module;
		$instelling->instelling = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$instelling->profiel = ProfielRepository::get($uid);

		$this->_em->persist($instelling);
		$this->_em->flush();
		return $instelling;
	}

	public function getDefault($module, $id)
	{
		return $this->getField(
			$module,
			$id,
			InstellingConfiguration::FIELD_DEFAULT
		);
	}

	public function getType($module, $id)
	{
		if ($this->hasKey($module, $id)) {
			return $this->getField($module, $id, InstellingConfiguration::FIELD_TYPE);
		} else {
			return null;
		}
	}

	/**
	 * @throws Exception
	 */
	public function saveAll()
	{
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
				$this->cache->delete($this->getCacheKey($module, $id, $this->getUid()));
				$instelling = new LidInstelling();
				$instelling->module = $module;
				$instelling->instelling = $id;
				$instelling->profiel = ProfielRepository::get($this->getUid());
				$instelling->waarde = $waarde;
				$this->_em->persist($instelling);
			}
		}
		$this->_em->flush();
	}

	public function isValidValue($module, $id, $waarde)
	{
		$options = $this->getTypeOptions($module, $id);
		switch ($this->getType($module, $id)) {
			case InstellingType::Enumeration:
				return isset($options[$waarde]) || in_array($waarde, $options);
			case InstellingType::Integer:
				return is_numeric($waarde) &&
					$waarde >= $options[0] &&
					$waarde <= $options[1];
			case InstellingType::String:
				return strlen($waarde) >= $options[0] &&
					strlen($waarde) <= $options[1] &&
					preg_match('/^[\w\-_\. ]*$/', $waarde);
			default:
				return false;
		}
	}

	public function getTypeOptions($module, $id)
	{
		return $this->getField($module, $id, InstellingConfiguration::FIELD_OPTIES);
	}

	public function resetForUser(Profiel $profiel)
	{
		$this->createQueryBuilder('i')
			->andWhere('i.profiel = :profiel')
			->setParameter('profiel', $profiel)
			->delete()
			->getQuery()
			->execute();
	}

	public function resetForAll($module, $id)
	{
		$this->createQueryBuilder('i')
			->andWhere('i.module = :module')
			->andWhere('i.instelling = :id')
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
	public function wijzigInstelling($module, $id, $waarde)
	{
		$instelling = $this->getInstelling($module, $id);
		$instelling->waarde = $waarde;
		$this->update($instelling);
		return $instelling;
	}

	/**
	 * @param LidInstelling $entity
	 * @throws CsrGebruikerException
	 */
	public function update($entity)
	{
		if (!$this->hasKey($entity->module, $entity->instelling)) {
			throw new CsrGebruikerException(
				"Instelling '{$entity->instelling}' uit module '{$entity->module}' niet gevonden."
			);
		}

		$this->cache->delete(
			$this->getCacheKey(
				$entity->module,
				$entity->instelling,
				$entity->profiel->uid
			)
		);

		$type = $this->getTypeOptions($entity->module, $entity->instelling);
		$typeOptions = $this->getTypeOptions($entity->module, $entity->instelling);

		if (
			$type === InstellingType::Enumeration &&
			!in_array($entity->waarde, $typeOptions)
		) {
			throw new CsrGebruikerException('Waarde is geen geldige optie');
		}

		if ($type === InstellingType::String) {
			if (strlen($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException('Waarde is te lang');
			}

			if (strlen($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException('Waarde is te kort');
			}
		}

		if ($type === InstellingType::Integer) {
			if (intval($entity->waarde) > $typeOptions[1]) {
				throw new CsrGebruikerException('Waarde is te lang');
			}

			if (intval($entity->waarde) < $typeOptions[0]) {
				throw new CsrGebruikerException('Waarde is te kort');
			}
		}

		$this->_em->flush();
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
	public function getInstellingVoorLid($module, $id, $uid)
	{
		return $this->getInstelling($module, $id, $uid)->waarde;
	}

	/**
	 */
	public function opschonen()
	{
		$instellingen = [];
		foreach ($this->getModules() as $module) {
			foreach ($this->getModuleKeys($module) as $instelling) {
				$instellingen[] = $instelling;
			}
		}

		$this->createQueryBuilder('i')
			->delete()
			->where(
				'i.module not in (:modules) or i.instelling not in (:instellingen)'
			)
			->setParameter('modules', $this->getModules())
			->setParameter('instellingen', $instellingen)
			->getQuery()
			->execute();
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @param string|null $uid
	 * @return string
	 */
	private function getCacheKey(string $module, string $id, ?string $uid): string
	{
		return 'lidInstelling_' . $module . '_' . $id . '_' . $uid;
	}
}
