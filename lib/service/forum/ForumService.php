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
	public function __construct(
		private readonly ForumDradenReagerenRepository $forumDradenReagerenRepository,
		private readonly ForumPostsRepository $forumPostsRepository,
		private readonly ForumDradenGelezenRepository $forumDradenGelezenRepository,
		private readonly ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		private readonly ForumDradenMeldingRepository $forumDradenMeldingRepository,
		private readonly ForumDelenMeldingRepository $forumDelenMeldingRepository,
		private readonly ForumDradenRepository $forumDradenRepository,
		private readonly ProfielRepository $profielRepository
	) {
	}

	public function opschonen()
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
