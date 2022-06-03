<?php

namespace CsrDelft\service\corvee;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveePuntenOverzichtDTO;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;

/**
 * CorveeToewijzenModel.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 */
class CorveeToewijzenService
{
    /**
     * @var CorveePuntenService
     */
    private $corveePuntenService;
    /**
     * @var CorveeVrijstellingenRepository
     */
    private $corveeVrijstellingenRepository;

    public function __construct(CorveeVrijstellingenRepository $corveeVrijstellingenModel, CorveePuntenService $corveePuntenService)
    {
        $this->corveePuntenService = $corveePuntenService;
        $this->corveeVrijstellingenRepository = $corveeVrijstellingenModel;
    }

    /**
     * Bepaald de suggesties voor het toewijzen van een corveetaak.
     * Als er een kwalificatie benodigd is worden alleen de
     * gekwalificeerde leden teruggegeven.
     *
     * @param CorveeTaak $taak
     * @return array
     * @throws CsrGebruikerException
     */
    public function getSuggesties(CorveeTaak $taak)
    {
        $vrijstellingen = $this->corveeVrijstellingenRepository->getAlleVrijstellingen(true); // grouped by uid
        $functie = $taak->corveeFunctie;
        if ($functie->kwalificatie_benodigd) { // laad alleen gekwalificeerde leden
            /** @var CorveePuntenOverzichtDTO[] $corveePuntenOverzichten */
            $corveePuntenOverzichten = [];
            $avg = 0;
            foreach ($functie->kwalificaties as $kwali) {
                $profiel = $kwali->profiel;
                if (!$profiel) {
                    throw new CsrGebruikerException(sprintf('Lid met uid "%s" bestaat niet.', $profiel->uid));
                }
                $uid = $kwali->profiel->uid;
                if (!$profiel->isLid()) {
                    continue; // geen oud-lid of overleden lid
                }
                if (array_key_exists($uid, $vrijstellingen)) {
                    $vrijstelling = $vrijstellingen[$uid];
                    if ($taak->datum >= $vrijstelling->begin_datum && $taak->datum <= $vrijstelling->eind_datum) {
                        continue; // taak valt binnen vrijstelling-periode: suggestie niet weergeven
                    }
                }
                $corveePuntenOverzichten[$uid] = $this->corveePuntenService->loadPuntenVoorLid($profiel, array($functie->functie_id => $functie));
                $corveePuntenOverzichten[$uid]->aantal = $corveePuntenOverzichten[$uid]->aantallen[$functie->functie_id];
                $avg += $corveePuntenOverzichten[$uid]->aantal;
            }
            $avg /= sizeof($corveePuntenOverzichten);
            foreach ($corveePuntenOverzichten as $uid => $punten) {
                $corveePuntenOverzichten[$uid]->relatief = $corveePuntenOverzichten[$uid]->aantal - (int)$avg;
            }
            $sorteer = 'sorteerKwali';
        } else {
            $corveePuntenOverzichten = $this->corveePuntenService->loadPuntenVoorAlleLeden();
            foreach ($corveePuntenOverzichten as $uid => $punten) {
                if (array_key_exists($uid, $vrijstellingen)) {
                    $vrijstelling = $vrijstellingen[$uid];
                    $datum = $taak->datum;
                    if ($datum >= $vrijstelling->begin_datum && $datum <= $vrijstelling->eind_datum) {
                        unset($corveePuntenOverzichten[$uid]); // taak valt binnen vrijstelling-periode: suggestie niet weergeven
                    }
                    // corrigeer prognose in suggestielijst vóór de aanvang van de vrijstellingsperiode
                    if ($vrijstelling !== null && $datum < $vrijstelling->begin_datum) {
                        $corveePuntenOverzichten[$uid]->prognose -= $vrijstelling->getPunten();
                    }
                }
            }
            $sorteer = 'sorteerPrognose';
        }
        foreach ($corveePuntenOverzichten as $uid => $punten) {
            $corveePuntenOverzichten[$uid]->laatste = ContainerFacade::getContainer()->get(CorveeTakenRepository::class)->getLaatsteTaakVanLid($uid);
            if ($corveePuntenOverzichten[$uid]->laatste !== null && $corveePuntenOverzichten[$uid]->laatste->getBeginMoment() >= strtotime(instelling('corvee', 'suggesties_recent_verbergen'), $taak->getBeginMoment())) {
                $corveePuntenOverzichten[$uid]->recent = true;
            } else {
                $corveePuntenOverzichten[$uid]->recent = false;
            }
            if ($taak->corveeRepetitie !== null) {
                $corveePuntenOverzichten[$uid]->voorkeur = ContainerFacade::getContainer()->get(CorveeVoorkeurenRepository::class)->getHeeftVoorkeur($taak->corveeRepetitie->crv_repetitie_id, $uid);
            } else {
                $corveePuntenOverzichten[$uid]->voorkeur = false;
            }
        }
        uasort($corveePuntenOverzichten, [$this, $sorteer]);
        return $corveePuntenOverzichten;
    }

    /**
     * Langst geleden bovenaan. Bij geen laatste taken op aantal.
     * @param CorveePuntenOverzichtDTO $a
     * @param CorveePuntenOverzichtDTO $b
     * @return int
     */
    public function sorteerKwali(CorveePuntenOverzichtDTO $a, CorveePuntenOverzichtDTO $b)
    {
        if (!$a->laatste && !$b->laatste) {
            $a = $a->aantal;
            $b = $b->aantal;
        } elseif (!$a->laatste) {
            return -1;
        } elseif (!$b->laatste) {
            return 1;
        } else {
            $a = $a->laatste->getBeginMoment();
            $b = $b->laatste->getBeginMoment();
        }
        if ($a === $b) {
            return 0;
        } elseif ($a < $b) { // < ASC
            return -1;
        } else {
            return 1;
        }
    }

    public function sorteerPrognose(CorveePuntenOverzichtDTO $a, CorveePuntenOverzichtDTO $b)
    {
        $a = $a->prognose;
        $b = $b->prognose;
        if ($a === $b) {
            return 0;
        } elseif ($a < $b) { // < ASC
            return -1;
        } else {
            return 1;
        }
    }
}
