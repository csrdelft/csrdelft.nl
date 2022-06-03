<?php

namespace CsrDelft\repository\aanmelder;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\entity\aanmelder\Deelnemer;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Deelnemer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deelnemer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deelnemer[]    findAll()
 * @method Deelnemer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeelnemerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deelnemer::class);
    }

    public function getAantalAanmeldingen(AanmeldActiviteit $activiteit): int
    {
        $q = $this->createQueryBuilder('a')
            ->select('SUM(a.aantal)')
            ->where('a.activiteit = :activiteit')
            ->setParameter('activiteit', $activiteit)
            ->getQuery();

        try {
            return $q->getSingleScalarResult() ?? 0;
        } catch (NoResultException|NonUniqueResultException $e) {
            return 0;
        }
    }

    public function isAangemeld(AanmeldActiviteit $activiteit, Profiel $profiel): bool
    {
        return $this->getDeelnemer($activiteit, $profiel) !== null;
    }

    public function getAantalGasten(AanmeldActiviteit $activiteit, Profiel $profiel): int
    {
        if (!$this->isAangemeld($activiteit, $profiel)) return 0;
        return $this->getDeelnemer($activiteit, $profiel)->getAantal() - 1;
    }

    public function getDeelnemer(AanmeldActiviteit $activiteit, Profiel $profiel): ?Deelnemer
    {
        return $this->findOneBy(['activiteit' => $activiteit, 'lid' => $profiel]);
    }

    /**
     * @param AanmeldActiviteit $activiteit
     * @param Profiel $lid
     * @param int $aantal
     * @param bool $beheer
     * @return Deelnemer
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function aanmelden(AanmeldActiviteit $activiteit, Profiel $lid, int $aantal, bool $beheer = false): Deelnemer
    {
        $reden = '';
        if (!$activiteit->magAanmelden($aantal, $reden) && !$beheer) {
            throw new CsrGebruikerException("Aanmelden mislukt: {$reden}.");
        } elseif ($this->isAangemeld($activiteit, $lid)) {
            throw new CsrGebruikerException("Aanmelden mislukt: al aangemeld.");
        } elseif ($aantal < 1) {
            throw new CsrGebruikerException("Aanmelden mislukt: aantal moet minimaal 1 zijn.");
        } elseif ($aantal > $activiteit->getMaxAantal() && !$beheer) {
            throw new CsrGebruikerException("Aanmelden mislukt: niet meer dan {$activiteit->getMaxGasten()} gasten.");
        }

        $deelnemer = new Deelnemer($activiteit, $lid, $aantal);

        $this->getEntityManager()->persist($deelnemer);
        $this->getEntityManager()->flush();
        return $deelnemer;
    }

    /**
     * @param AanmeldActiviteit $activiteit
     * @param Profiel $lid
     * @throws ORMException
     */
    public function afmelden(AanmeldActiviteit $activiteit, Profiel $lid, bool $beheer = false): void
    {
        $reden = '';
        if (!$this->isAangemeld($activiteit, $lid)) {
            throw new CsrGebruikerException("Afmelden mislukt: niet aangemeld.");
        } elseif (!$activiteit->magAfmelden($reden) && !$beheer) {
            throw new CsrGebruikerException("Afmelden mislukt: {$reden}.");
        }

        $deelnemer = $this->getDeelnemer($activiteit, $lid);
        $this->getEntityManager()->remove($deelnemer);
        $this->getEntityManager()->flush();
    }

    /**
     * @param AanmeldActiviteit $activiteit
     * @param Profiel $lid
     * @param int $aantal
     * @param bool $beheer
     * @return Deelnemer
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function aantalAanpassen(AanmeldActiviteit $activiteit, Profiel $lid, int $aantal, bool $beheer = false): Deelnemer
    {
        if (!$this->isAangemeld($activiteit, $lid)) {
            throw new CsrGebruikerException("Gasten aanpassen mislukt: niet aangemeld.");
        } elseif ($aantal < 1) {
            throw new CsrGebruikerException("Aanmelden mislukt: aantal moet minimaal 1 zijn.");
        } elseif ($aantal > $activiteit->getMaxAantal() && !$beheer) {
            throw new CsrGebruikerException("Aanmelden mislukt: niet meer dan {$activiteit->getMaxGasten()} gasten.");
        }

        $deelnemer = $this->getDeelnemer($activiteit, $lid);
        $reden = '';
        if ($deelnemer->getAantal() > $aantal) {
            $extra = $aantal - $deelnemer->getAantal();
            if (!$activiteit->magAanmelden($extra, $reden) && !$beheer) {
                throw new CsrGebruikerException("Gasten aanpassen mislukt: {$reden}.");
            }
        } elseif ($deelnemer->getAantal() < $aantal) {
            if (!$activiteit->magAfmelden($reden) && !$beheer) {
                throw new CsrGebruikerException("Gasten aanpassen mislukt: {$reden}.");
            }
        } else {
            return $deelnemer;
        }

        $deelnemer->setAantal($aantal);
        $this->getEntityManager()->flush();
        return $deelnemer;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function setAanwezig(AanmeldActiviteit $activiteit, Profiel $lid, $aanwezig = true): Deelnemer
    {
        if (!$this->isAangemeld($activiteit, $lid)) {
            throw new CsrGebruikerException("Aanwezig melden mislukt: niet aangemeld.");
        }

        $deelnemer = $this->getDeelnemer($activiteit, $lid);
        if ($aanwezig && !$deelnemer->isAanwezig()) {
            $deelnemer->setAanwezig();
        } elseif (!$aanwezig && $deelnemer->isAanwezig()) {
            $deelnemer->setNietAanwezig();
        }
        $this->getEntityManager()->flush();
        return $deelnemer;
    }
}
