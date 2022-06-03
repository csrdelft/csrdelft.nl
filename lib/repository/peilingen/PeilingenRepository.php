<?php

namespace CsrDelft\repository\peilingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\peilingen\PeilingStem;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
 *
 * @method Peiling|null find($id, $lockMode = null, $lockVersion = null)
 * @method Peiling|null findOneBy(array $criteria, array $orderBy = null)
 * @method Peiling[]    findAll()
 * @method Peiling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Peiling|null retrieveByUuid($UUID)
 */
class PeilingenRepository extends AbstractRepository {
	/**
	 * @var PeilingOptiesRepository
	 */
	private $peilingOptiesModel;
	/**
	 * @var PeilingStemmenRepository
	 */
	private $peilingStemmenModel;

	public function __construct(PeilingOptiesRepository $peilingOptiesRepository, PeilingStemmenRepository $peilingStemmenRepository, ManagerRegistry $registry) {
		parent::__construct($registry, Peiling::class);

		$this->peilingOptiesModel = $peilingOptiesRepository;
		$this->peilingStemmenModel = $peilingStemmenRepository;
	}

	/**
	 * @param Peiling $entity
	 * @return void
	 */
	public function delete(Peiling $entity) {
		$manager = $this->getEntityManager();

		$manager->beginTransaction();
		try {

			foreach ($entity->opties as $optie) {
				$manager->remove($optie);
			}

			$stemmen = $this->peilingStemmenModel->findBy(['peiling_id' => $entity->id]);
			foreach ($stemmen as $stem) {
				$manager->remove($stem);
			}

			$manager->remove($entity);
			$manager->flush();
			$manager->commit();
		} catch (ORMException $ex) {
			$manager->rollback();

			throw new CsrException($ex->getMessage());
		}
	}

	/**
	 * @param Peiling $entity
	 * @return string
	 */
	public function create(Peiling $entity) {
		$manager = $this->getEntityManager();

		$manager->persist($entity);
		$manager->flush();

		return $entity->id;
	}

	/**
	 * @param int $peiling_id
	 * @param int $optie_id
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function stem($peiling_id, $optie_id) {
		$peiling = $this->getPeilingById((int)$peiling_id);
		if ($peiling->getMagStemmen() && !$this->peilingStemmenModel->heeftGestemd($peiling_id, $optie_id)) {
			$optie = $this->peilingOptiesModel->findOneBy(['peiling_id' => $peiling_id, 'id' => $optie_id]);
			if (!$optie) {
				throw new CsrGebruikerException('Peiling optie bestaat niet.');
			}

			$optie->stemmen += 1;

			$stem = new PeilingStem();
			$stem->peiling_id = $peiling->id;
			$stem->uid = LoginService::getUid();
			$stem->profiel = LoginService::getProfiel();

			$manager = $this->getEntityManager();

			$manager->persist($stem);
			$manager->persist($optie);
			$manager->flush();
		} else {
			setMelding("Stemmen niet toegestaan", -1);
		}
	}

	/**
	 * @param $peiling_id
	 * @return Peiling|false
	 */
	public function getPeilingById($peiling_id) {
		return $this->find($peiling_id);
	}

	/**
	 * @param Peiling $entity
	 *
	 * @return string
	 * @throws CsrGebruikerException
	 */
	public function validate(Peiling $entity) {
		$errors = '';
		if ($entity == null) {
			throw new CsrGebruikerException('Peiling is leeg');
		}
		if (trim($entity->beschrijving) == '') {
			$errors .= 'Tekst mag niet leeg zijn.<br />';
		}
		if (trim($entity->titel) == '') {
			$errors .= 'Titel mag niet leeg zijn.<br />';
		}
		if (count($entity->opties) == 0) {
			$errors .= 'Er moet tenminste 1 optie zijn.<br />';
		}
		return $errors;
	}

	public function getPeilingenVoorBeheer() {

		$peilingen = $this->findAll();
		if (LoginService::mag(P_PEILING_MOD)) {
			return $peilingen;
		} else {
			$zichtbarePeilingen = $this->findBy(['eigenaar' => LoginService::getUid()]);
			$peilingenMetRechten = $this->createQueryBuilder('p')
				->andWhere('p.eigenaar <> :uid')
				->andWhere('p.rechten_mod <> :rechten')
				->setParameter('uid', LoginService::getUid())
				->setParameter('rechten', '')
				->getQuery()->getResult();
			foreach ($peilingenMetRechten as $peiling) {
				if (LoginService::mag($peiling->rechten_mod)) {
					$zichtbarePeilingen[] = $peiling;
				}
			}

			return $zichtbarePeilingen;
		}
	}

	public function magBewerken($peiling) {
		if (LoginService::mag(P_PEILING_MOD)
			|| $peiling->eigenaar == LoginService::getUid()
			|| LoginService::mag($peiling->rechten_mod)) {
			return $peiling;
		}

		return false;
	}

	/**
	 * @return Peiling[]
	 */
	public function getLijst() {
		return $this->findBy([], ['id' => 'DESC']);
	}
}
