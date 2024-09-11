<?php

namespace CsrDelft\repository\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\instellingen\InstellingConfiguration;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\entity\Instelling;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * InstellingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method Instelling|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instelling[]    findAll()
 * @method Instelling|null find($id, $lockMode = null, $lockVersion = null)
 * @method Instelling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstellingenRepository extends AbstractRepository
{
	use YamlInstellingen;

	/**
	 * InstellingenModel constructor.
	 * @param ManagerRegistry $manager
	 * @param CacheInterface $cache
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws LoaderLoadException
	 */
	public function __construct(
		ManagerRegistry $manager,
		private CacheInterface $cache
	) {
		parent::__construct($manager, Instelling::class);

		$this->load(
			'instellingen/stek_instelling.yaml',
			new InstellingConfiguration()
		);
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getValue($module, $id)
	{
		return $this->getInstelling($module, $id)->waarde;
	}

	/**
	 * Haal een instelling op uit het cache of de database.
	 * Als een instelling niet is gezet wordt deze aangemaakt met de default waarde en opgeslagen.
	 *
	 * @param string $module
	 * @param string $id
	 * @return Instelling
	 * @throws CsrException indien de default waarde ontbreekt (de instelling bestaat niet)
	 */
	public function getInstelling($module, $id)
	{
		return $this->cache->get($this->getCacheKey($module, $id), function () use (
			$module,
			$id
		) {
			$entity = $this->findOneBy(['module' => $module, 'instelling' => $id]);
			if ($this->hasKey($module, $id) && $entity != null) {
				return $entity;
			} elseif ($this->hasKey($module, $id)) {
				return $this->newInstelling($module, $id);
			} else {
				if ($entity != null) {
					$entityManager = $this->getEntityManager();
					$entityManager->remove($entity);
					$entityManager->flush();
				}
				throw new CsrException(
					sprintf('Instelling bestaat niet: "%s" module: "%s".', $id, $module)
				);
			}
		});
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return Instelling
	 */
	protected function newInstelling($module, $id)
	{
		$instelling = new Instelling();
		$instelling->module = $module;
		$instelling->instelling = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$entityManager = $this->getEntityManager();
		$entityManager->persist($instelling);
		$entityManager->flush();
		return $instelling;
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getDefault($module, $id)
	{
		return $this->getField(
			$module,
			$id,
			InstellingConfiguration::FIELD_DEFAULT
		);
	}

	/**
	 * @param string $module
	 * @param string $id
	 * @param string $waarde
	 *
	 * @return Instelling
	 */
	public function wijzigInstelling($module, $id, $waarde)
	{
		// Skip de cache
		$instelling = $this->findOneBy(['module' => $module, 'instelling' => $id]);

		if ($instelling == null) {
			$instelling = new Instelling();
			$instelling->module = $module;
			$instelling->instelling = $id;
			$this->_em->persist($instelling);
		}

		$instelling->waarde = $waarde;

		$this->cache->delete($this->getCacheKey($module, $id));
		$this->_em->flush();
		return $instelling;
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
	 * @return string
	 */
	private function getCacheKey(string $module, string $id): string
	{
		return 'instelling_' . $module . '_' . $id;
	}
}
