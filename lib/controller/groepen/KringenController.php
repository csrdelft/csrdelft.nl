<?php

namespace CsrDelft\controller\groepen;

use CsrDelft\entity\groepen\Kring;
use CsrDelft\repository\groepen\KringenRepository;
use CsrDelft\view\Icon;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * KringenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor kringen.
 *
 * @property KringenRepository $repository
 */
class KringenController extends AbstractGroepenController
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kring::class);
    }

    public function zoeken(Request $request, $zoekterm = null)
    {
        if (!$zoekterm && !$request->query->has('q')) {
            throw $this->createAccessDeniedException();
        }
        if (!$zoekterm) {
            $zoekterm = $request->query->get('q');
        }
        $zoekterm = '%' . $zoekterm . '%';
        $limit = 5;
        if ($request->query->has('limit')) {
            $limit = $request->query->getInt('limit');
        }
        $result = array();
        $kringen = $this->repository->createQueryBuilder('k')
            ->where('k.naam LIKE :zoekterm')
            ->setParameter('zoekterm', sql_contains($zoekterm))
            ->setMaxResults($limit)
            ->getQuery()->getResult();
        foreach ($kringen as $kring) {
            /** @var Kring $kring */
            $result[] = array(
                'url' => $kring->getUrl() . '#' . $kring->id,
                'label' => $kring->familie,
                'icon' => Icon::getTag('Kring'),
                'value' => 'Kring:' . $kring->verticale . '.' . $kring->kringNummer
            );
        }
        return new JsonResponse($result);
    }

}
