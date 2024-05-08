<?php

namespace CsrDelft\repository\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\entity\commissievoorkeuren\VoorkeurVoorkeur;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CommissieVoorkeurRepository
 * @package CsrDelft\repository\commissievoorkeuren
 * @method VoorkeurVoorkeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoorkeurVoorkeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoorkeurVoorkeur[]    findAll()
 * @method VoorkeurVoorkeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissieVoorkeurRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, VoorkeurVoorkeur::class);
	}

	/**
	 * @param Profiel $profiel
	 * @return VoorkeurVoorkeur[]|false
	 */
	public function getVoorkeurenVoorLid(Profiel $profiel): array
	{
		return $this->findBy(['uid' => $profiel->uid]);
	}

	/**
	 * @param VoorkeurCommissie $commissie
	 * @param int $minVoorkeurWaarde
	 * @return VoorkeurVoorkeur[]|false
	 */
	public function getVoorkeurenVoorCommissie(VoorkeurCommissie $commissie, int $minVoorkeurWaarde = 1): mixed {
		$qb = $this->createQueryBuilder('v');
		$qb->andWhere('v.cid = :cid');
		$qb->andWhere('v.voorkeur >= :minVoorkeur');
		$qb->setParameters([
			'cid' => $commissie->id,
			'minVoorkeur' => $minVoorkeurWaarde,
		]);

		return $qb->getQuery()->getResult();
	}

	/**
	 * @param Profiel $profiel
	 * @param VoorkeurCommissie $commissie
	 * @return VoorkeurVoorkeur|null
	 */
	public function getVoorkeur(Profiel $profiel, VoorkeurCommissie $commissie): ?VoorkeurVoorkeur
	{
		$voorkeur = $this->find(['uid' => $profiel->uid, 'cid' => $commissie->id]);
		if ($voorkeur == null) {
			$voorkeur = new VoorkeurVoorkeur();
			$voorkeur->setProfiel($profiel);
			$voorkeur->setCommissie($commissie);
			$voorkeur->voorkeur = 1;
			$voorkeur->timestamp = date_create_immutable();
			$this->getEntityManager()->persist($voorkeur);
		}
		return $voorkeur;
	}
}
