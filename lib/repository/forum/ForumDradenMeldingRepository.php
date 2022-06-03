<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Mail;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMelding;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\MailService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Environment;

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

	public function setNiveauVoorLid(ForumDraad $draad, ForumDraadMeldingNiveau $niveau)
	{
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

	protected function maakForumDraadMelding(ForumDraad $draad, $uid, ForumDraadMeldingNiveau $niveau)
	{
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
			->getQuery()->execute();
	}

	public function stopMeldingenVoorIedereen(array $draadIds)
	{
		$this->createQueryBuilder('m')
			->where('m.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->delete()
			->getQuery()->execute();
	}

	public function getAltijdMeldingVoorDraad(ForumDraad $draad)
	{
		return $this->findBy(['draad_id' => $draad->draad_id, 'niveau' => ForumDraadMeldingNiveau::ALTIJD()]);
	}
}
