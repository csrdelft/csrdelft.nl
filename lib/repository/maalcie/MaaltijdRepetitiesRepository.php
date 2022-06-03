<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

/**
 * MaaltijdRepetitiesRepository  |  P.W.G. Brussee (brussee@live.nl)
 *
 * @method MaaltijdRepetitie|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdRepetitie|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdRepetitie[]    findAll()
 * @method MaaltijdRepetitie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdRepetitiesRepository extends AbstractRepository
{
    protected $default_order = '(periode_in_dagen = 0) ASC, periode_in_dagen ASC, dag_vd_week ASC, standaard_titel ASC';
    /**
     * @var MaaltijdAanmeldingenRepository
     */
    private $maaltijdAanmeldingenRepository;

    public function __construct(ManagerRegistry $registry, MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository)
    {
        parent::__construct($registry, MaaltijdRepetitie::class);

        $this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
    }

    /**
     * Filtert de repetities met het abonnement-filter van de maaltijd-repetitie op de permissies van het ingelogde lid.
     *
     * @param string $uid
     * @return MaaltijdRepetitie[]
     * @internal param MaaltijdRepetitie[] $repetities
     */
    public function getAbonneerbareRepetitiesVoorLid($uid)
    {
        $repetities = $this->findBy(['abonneerbaar' => 'true']);
        $result = array();
        foreach ($repetities as $repetitie) {
            if ($this->maaltijdAanmeldingenRepository->checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
                $result[$repetitie->mlt_repetitie_id] = $repetitie;
            }
        }
        return $result;
    }

    public function getAlleRepetities($groupById = false)
    {
        $repetities = $this->findAll();
        if ($groupById) {
            $result = array();
            foreach ($repetities as $repetitie) {
                $result[$repetitie->mlt_repetitie_id] = $repetitie;
            }
            return $result;
        }
        return $repetities;
    }

    /**
     * @param $mrid
     * @return MaaltijdRepetitie
     * @throws CsrGebruikerException
     */
    public function getRepetitie($mrid)
    {
        $repetitie = $this->find($mrid);
        if (!$repetitie) {
            throw new CsrGebruikerException('Get maaltijd-repetitie faalt: Not found $mrid =' . $mrid);
        }
        return $repetitie;
    }

    /**
     * @param $repetitie MaaltijdRepetitie
     * @return array
     * @throws Throwable
     */
    public function saveRepetitie($repetitie)
    {
        return $this->_em->transactional(function () use ($repetitie) {
            $abos = 0;
            $this->_em->persist($repetitie);
            $this->_em->flush();
            if (!$repetitie->abonneerbaar) { // niet (meer) abonneerbaar
                $abos = ContainerFacade::getContainer()->get(MaaltijdAbonnementenRepository::class)->verwijderAbonnementen($repetitie);
            }
            return $abos;
        });
    }

    /**
     * @param MaaltijdRepetitie $repetitie
     * @return int
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function verwijderRepetitie(MaaltijdRepetitie $repetitie)
    {
        if (ContainerFacade::getContainer()->get(CorveeRepetitiesRepository::class)->existMaaltijdRepetitieCorvee($repetitie->mlt_repetitie_id)) {
            throw new CsrGebruikerException('Ontkoppel of verwijder eerst de bijbehorende corvee-repetities!');
        }
        $maaltijdenRepository = ContainerFacade::getContainer()->get(MaaltijdenRepository::class);
        if ($maaltijdenRepository->existRepetitieMaaltijden($repetitie->mlt_repetitie_id)) {
            $maaltijdenRepository->verwijderRepetitieMaaltijden($repetitie->mlt_repetitie_id); // delete maaltijden first (foreign key)
            throw new CsrGebruikerException('Alle bijbehorende maaltijden zijn naar de prullenbak verplaatst. Verwijder die eerst!');
        }
        $aantalAbos = ContainerFacade::getContainer()->get(MaaltijdAbonnementenRepository::class)->verwijderAbonnementen($repetitie);
        $this->_em->remove($repetitie);
        $this->_em->flush();
        return $aantalAbos;
    }
}
