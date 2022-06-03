<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\LidZoekerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiLedenController
{
    /**
     * @var LidZoekerService
     */
    private $lidZoekerService;

    public function __construct(LidZoekerService $lidZoekerService)
    {
        $this->lidZoekerService = $lidZoekerService;
    }

    /**
     * @Route("/API/2.0/leden", methods={"GET"})
     * @Auth(P_OUDLEDEN_READ)
     */
    public function getLeden()
    {
        $leden = [];

        foreach ($this->lidZoekerService->getLeden() as $profiel) {
            $leden[] = array(
                'id' => $profiel->uid,
                'voornaam' => $profiel->voornaam,
                'tussenvoegsel' => $profiel->tussenvoegsel,
                'achternaam' => $profiel->achternaam
            );
        }

        return new JsonResponse(array('data' => $leden));
    }

    /**
     * @Route("/API/2.0/leden/{id}", methods={"GET"})
     * @Auth(P_OUDLEDEN_READ)
     */
    public function getLid($id)
    {
        $profiel = ProfielRepository::get($id);

        if (!$profiel) {
            throw new NotFoundHttpException(404);
        }

        $woonoord = $profiel->getWoonoord();
        $lid = array(
            'id' => $profiel->uid,
            'naam' => array(
                'voornaam' => $profiel->voornaam,
                'tussenvoegsel' => $profiel->tussenvoegsel,
                'achternaam' => $profiel->achternaam,
                'formeel' => $profiel->getNaam('civitas')
            ),
            'pasfoto' => $profiel->getPasfotoPath('vierkant'),
            'geboortedatum' => date_format_intl($profiel->gebdatum, DATE_FORMAT),
            'email' => $profiel->email,
            'mobiel' => $profiel->mobiel,
            'huis' => array(
                'naam' => $woonoord ? $woonoord->naam : null,
                'adres' => $profiel->adres,
                'postcode' => $profiel->postcode,
                'woonplaats' => $profiel->woonplaats,
                'land' => $profiel->land
            ),
            'studie' => array(
                'naam' => $profiel->studie,
                'sinds' => $profiel->studiejaar
            ),
            'lichting' => $profiel->lidjaar,
            'verticale' => !$profiel->getVerticale() ? null : $profiel->getVerticale()->naam,
        );

        return new JsonResponse(array('data' => $lid));
    }

}
