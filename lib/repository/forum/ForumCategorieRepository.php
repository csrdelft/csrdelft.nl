<?php

namespace CsrDelft\repository\forum;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @method ForumCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumCategorieRepository extends AbstractRepository
{
    /**
     * Lazy loading
     * @var array
     */
    private $indeling;
    /**
     * @var ForumDelenRepository
     */
    private $forumDelenRepository;
    /**
     * @var ForumDradenRepository
     */
    private $forumDradenRepository;
    /**
     * @var ForumDradenGelezenRepository
     */
    private $forumDradenGelezenRepository;
    /**
     * @var ForumDradenReagerenRepository
     */
    private $forumDradenReagerenRepository;
    /**
     * @var ForumDradenVerbergenRepository
     */
    private $forumDradenVerbergenRepository;
    /**
     * @var ForumDradenMeldingRepository
     */
    private $forumDradenMeldingModel;
    /**
     * @var ForumDelenMeldingRepository
     */
    private $forumDelenMeldingRepository;
    /**
     * @var ForumPostsRepository
     */
    private $forumPostsRepository;

    public function __construct(
        ManagerRegistry                $managerRegistry,
        ForumDelenRepository           $forumDelenRepository,
        ForumDradenRepository          $forumDradenRepository,
        ForumDradenGelezenRepository   $forumDradenGelezenRepository,
        ForumDradenReagerenRepository  $forumDradenReagerenRepository,
        ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
        ForumDradenMeldingRepository   $forumDradenMeldingModel,
        ForumPostsRepository           $forumPostsRepository,
        ForumDelenMeldingRepository    $forumDelenMeldingRepository
    )
    {
        parent::__construct($managerRegistry, ForumCategorie::class);

        $this->forumDelenRepository = $forumDelenRepository;
        $this->forumDradenRepository = $forumDradenRepository;
        $this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
        $this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
        $this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
        $this->forumDradenMeldingModel = $forumDradenMeldingModel;
        $this->forumPostsRepository = $forumPostsRepository;
        $this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
    }

    public function get($id)
    {
        $categorie = $this->find($id);
        if (!$categorie) {
            throw new CsrGebruikerException('Forum-categorie bestaat niet!');
        }
        return $categorie;
    }

    public function findAll()
    {
        return $this->findBy([], ['volgorde' => 'ASC']);
    }

    /**
     * Eager loading of ForumDeel[].
     *
     * @return ForumCategorie[]
     */
    public function getForumIndelingVoorLid()
    {
        if (!isset($this->indeling)) {
            $delenByCategorieId = group_by('categorie_id', $this->forumDelenRepository->getForumDelenVoorLid());
            $this->indeling = array();
            foreach ($this->findAll() as $categorie) {
                /** @var ForumCategorie $categorie */
                if ($categorie->magLezen()) {
                    $this->indeling[] = $categorie;
                    if (isset($delenByCategorieId[$categorie->categorie_id])) {
                        $categorie->setForumDelen($delenByCategorieId[$categorie->categorie_id]);
                    } else {
                        $categorie->setForumDelen(array());
                    }
                }
            }
        }
        return $this->indeling;
    }

    public function opschonen()
    {
        // Oude lege concepten verwijderen
        $this->forumDradenReagerenRepository->verwijderLegeConcepten();

        // Niet-goedgekeurde posts verwijderen

        $this->forumPostsRepository->createQueryBuilder('fp')
            ->delete()
            ->where('fp.verwijderd = true and fp.wacht_goedkeuring = true')
            ->getQuery()->execute();

        // Voor alle ex-leden settings opschonen
        $profielen = $this->_em->getRepository(Profiel::class)->createQueryBuilder('p')
            ->select('p.uid')
            ->where('p.status in (:status)')
            ->setParameter('status', array(LidStatus::Commissie, LidStatus::Nobody, LidStatus::Exlid, LidStatus::Overleden))
            ->getQuery()->getArrayResult();

        $uids = array_column($profielen, 'uid');
        $this->forumDradenGelezenRepository->verwijderDraadGelezenVoorLeden($uids);
        $this->forumDradenVerbergenRepository->toonAllesVoorLeden($uids);
        $this->forumDradenMeldingModel->stopAlleMeldingenVoorLeden($uids);
        $this->forumDelenMeldingRepository->stopAlleMeldingenVoorLeden($uids);
        $this->forumDradenReagerenRepository->verwijderReagerenVoorLeden($uids);

        // Settings voor oude topics opschonen en oude/verwijderde topics en posts definitief verwijderen
        /** @var ForumDraad[] $draden */
        $draden = $this->forumDradenRepository->createQueryBuilder('fd')
            ->select('fd.draad_id')
            ->where('fd.verwijderd = true or (fd.gesloten = true and (fd.laatst_gewijzigd is null or fd.laatst_gewijzigd < :laatst_gewijzigd))')
            ->setParameter('laatst_gewijzigd', date_create_immutable('-1 year'))
            ->getQuery()->getArrayResult();
        $draadIds = array_column($draden, 'draad_id');

        // Settings verwijderen
        $this->forumDradenMeldingModel->stopMeldingenVoorIedereen($draadIds);
        $this->forumDradenVerbergenRepository->toonDraadVoorIedereen($draadIds);
        $this->forumDradenGelezenRepository->verwijderDraadGelezen($draadIds);
        $this->forumDradenReagerenRepository->verwijderReagerenVoorDraad($draadIds);

        $this->forumDradenRepository->createQueryBuilderWithoutOrder('fd')
            ->update()
            ->where('fd.draad_id in (?2)')
            ->set('fd.laatste_post_id', '?1')
            ->setParameter(1, null)
            ->setParameter(2, $draadIds)
            ->getQuery()
            ->execute();

        // Oude verwijderde posts definitief verwijderen
        $this->forumPostsRepository->createQueryBuilder('fp')
            ->delete()
            ->where('fp.draad_id in (:draad_ids)')
            ->setParameter('draad_ids', $draadIds)
            ->getQuery()
            ->execute();

        // Verwijder corresponderende draden
        $this->forumDradenRepository->createQueryBuilderWithoutOrder('fd')
            ->delete()
            ->where('fd.draad_id in (:draad_ids)')
            ->setParameter('draad_ids', $draadIds)
            ->getQuery()
            ->execute();
    }
}
