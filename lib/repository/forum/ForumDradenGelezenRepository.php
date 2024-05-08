<?php

namespace CsrDelft\repository\forum;

use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadGelezen;
use CsrDelft\repository\AbstractRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * ForumDradenGelezenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 * @method ForumDraadGelezen|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumDraadGelezen|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumDraadGelezen[]    findAll()
 * @method ForumDraadGelezen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumDradenGelezenRepository extends AbstractRepository
{
	public function __construct(
		ManagerRegistry $registry,
		private readonly Security $security
	) {
		parent::__construct($registry, ForumDraadGelezen::class);
	}

	protected function maakForumDraadGelezen(ForumDraad $draad)
	{
		$gelezen = new ForumDraadGelezen();
		$gelezen->draad = $draad;
		$gelezen->draad_id = $draad->draad_id; // Set pk
		$gelezen->uid = $this->security->getUser()->getUserIdentifier();
		$gelezen->profiel = $this->security->getUser()->profiel;
		$gelezen->datum_tijd = date_create_immutable();
		return $gelezen;
	}

	/**
	 * Ga na welke posts op de huidige pagina het laatst is geplaatst of gewijzigd.
	 *
	 * @param ForumDraad $draad
	 * @param DateTime $moment
	 */
	public function setWanneerGelezenDoorLid(ForumDraad $draad, $moment = null)
	{
		$gelezen = $this->find([
			'draad_id' => $draad->draad_id,
			'uid' => $this->security->getUser()->getUserIdentifier(),
		]);
		if (!$gelezen) {
			$gelezen = $this->maakForumDraadGelezen($draad);
			$this->getEntityManager()->persist($gelezen);
		}
		if ($moment) {
			$gelezen->datum_tijd = $moment;
		} else {
			foreach ($draad->getForumPosts() as $post) {
				if ($post->laatst_gewijzigd > $gelezen->datum_tijd) {
					$gelezen->datum_tijd = $post->laatst_gewijzigd;
				}
			}
		}

		$this->getEntityManager()->flush();

		$this->getEntityManager()->clear();
	}

	public function verwijderDraadGelezen(array $draadIds)
	{
		$this->createQueryBuilder('fdg')
			->delete()
			->where('fdg.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->getQuery()
			->execute();
	}

	public function verwijderDraadGelezenVoorLeden(array $uids)
	{
		$this->createQueryBuilder('fdg')
			->delete()
			->where('fdg.uid in (:uids)')
			->setParameter('uids', $uids)
			->getQuery()
			->execute();
	}
}
