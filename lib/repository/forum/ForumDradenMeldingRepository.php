<?php

namespace CsrDelft\repository\forum;

use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMelding;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Model voor bijhouden, bewerken en verzenden van meldingen voor forumberichten
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 * @method ForumDraadMelding|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadMelding|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadMelding[]    findAll()
 * @method ForumDraadMelding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenMeldingRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ForumDraadMelding::class);
	}

	public function setNiveauVoorLid(
		ForumDraad $draad,
		ForumDraadMeldingNiveau $niveau
	) {
		$uid = LoginService::getUid();
		$voorkeur = $this->find(['draad_id' => $draad->draad_id, 'uid' => $uid]);
		if ($voorkeur) {
			$voorkeur->niveau = $niveau;
			$this->getEntityManager()->persist($voorkeur);
			$this->getEntityManager()->flush();
		} else {
			$this->maakForumDraadMelding($draad, $uid, $niveau);
		}
	}

	protected function maakForumDraadMelding(
		ForumDraad $draad,
		$uid,
		ForumDraadMeldingNiveau $niveau
	) {
		$melding = new ForumDraadMelding();
		$melding->draad = $draad;
		$melding->draad_id = $draad->draad_id;
		$melding->uid = $uid;
		$melding->niveau = $niveau;

		$this->getEntityManager()->persist($melding);
		$this->getEntityManager()->flush();
		return $melding;
	}

	public function stopAlleMeldingenVoorLeden(array $uids)
	{
		$this->createQueryBuilder('m')
			->where('m.uid in (:uids)')
			->setParameter('uids', $uids)
			->delete()
			->getQuery()
			->execute();
	}

	public function stopMeldingenVoorIedereen(array $draadIds)
	{
		$this->createQueryBuilder('m')
			->where('m.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->delete()
			->getQuery()
			->execute();
	}

	public function getAltijdMeldingVoorDraad(ForumDraad $draad)
	{
		return $this->findBy([
			'draad_id' => $draad->draad_id,
			'niveau' => ForumDraadMeldingNiveau::ALTIJD(),
		]);
	}
}
