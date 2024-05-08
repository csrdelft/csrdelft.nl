<?php

namespace CsrDelft\service\forum;

use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\ProfielRepository;

class ForumService
{
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingRepository;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;

	public function __construct(
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumPostsRepository $forumPostsRepository,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		ForumDradenMeldingRepository $forumDradenMeldingRepository,
		ForumDelenMeldingRepository $forumDelenMeldingRepository,
		ForumDradenRepository $forumDradenRepository,
		ProfielRepository $profielRepository
	) {
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->profielRepository = $profielRepository;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumDradenMeldingRepository = $forumDradenMeldingRepository;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
		$this->forumDradenRepository = $forumDradenRepository;
	}

	public function opschonen(): void
	{
		// Oude lege concepten verwijderen
		$this->forumDradenReagerenRepository->verwijderLegeConcepten();

		// Niet-goedgekeurde posts verwijderen

		$this->forumPostsRepository
			->createQueryBuilder('fp')
			->delete()
			->where('fp.verwijderd = true and fp.wacht_goedkeuring = true')
			->getQuery()
			->execute();

		// Voor alle ex-leden settings opschonen
		$profielen = $this->profielRepository
			->createQueryBuilder('p')
			->select('p.uid')
			->where('p.status in (:status)')
			->setParameter('status', [
				LidStatus::Commissie,
				LidStatus::Nobody,
				LidStatus::Exlid,
				LidStatus::Overleden,
			])
			->getQuery()
			->getArrayResult();

		$uids = array_column($profielen, 'uid');
		$this->forumDradenGelezenRepository->verwijderDraadGelezenVoorLeden($uids);
		$this->forumDradenVerbergenRepository->toonAllesVoorLeden($uids);
		$this->forumDradenMeldingRepository->stopAlleMeldingenVoorLeden($uids);
		$this->forumDelenMeldingRepository->stopAlleMeldingenVoorLeden($uids);
		$this->forumDradenReagerenRepository->verwijderReagerenVoorLeden($uids);

		// Settings voor oude topics opschonen en oude/verwijderde topics en posts definitief verwijderen
		/** @var ForumDraad[] $draden */
		$draden = $this->forumDradenRepository
			->createQueryBuilder('fd')
			->select('fd.draad_id')
			->where(
				'fd.verwijderd = true or (fd.gesloten = true and (fd.laatst_gewijzigd is null or fd.laatst_gewijzigd < :laatst_gewijzigd))'
			)
			->setParameter('laatst_gewijzigd', date_create_immutable('-1 year'))
			->getQuery()
			->getArrayResult();
		$draadIds = array_column($draden, 'draad_id');

		// Settings verwijderen
		$this->forumDradenMeldingRepository->stopMeldingenVoorIedereen($draadIds);
		$this->forumDradenVerbergenRepository->toonDraadVoorIedereen($draadIds);
		$this->forumDradenGelezenRepository->verwijderDraadGelezen($draadIds);
		$this->forumDradenReagerenRepository->verwijderReagerenVoorDraad($draadIds);

		$this->forumDradenRepository
			->createQueryBuilderWithoutOrder('fd')
			->update()
			->where('fd.draad_id in (?2)')
			->set('fd.laatste_post_id', '?1')
			->setParameter(1, null)
			->setParameter(2, $draadIds)
			->getQuery()
			->execute();

		// Oude verwijderde posts definitief verwijderen
		$this->forumPostsRepository
			->createQueryBuilder('fp')
			->delete()
			->where('fp.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->getQuery()
			->execute();

		// Verwijder corresponderende draden
		$this->forumDradenRepository
			->createQueryBuilderWithoutOrder('fd')
			->delete()
			->where('fd.draad_id in (:draad_ids)')
			->setParameter('draad_ids', $draadIds)
			->getQuery()
			->execute();
	}
}
