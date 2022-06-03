<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\view\maalcie\forms\VrijstellingForm;
use CsrDelft\view\PlainView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVrijstellingenController extends AbstractController
{
    /** @var CorveeVrijstellingenRepository */
    private $corveeVrijstellingenRepository;

    public function __construct(CorveeVrijstellingenRepository $corveeVrijstellingenRepository)
    {
        $this->corveeVrijstellingenRepository = $corveeVrijstellingenRepository;
    }

    /**
     * @return Response
     * @Route("/corvee/vrijstellingen", methods={"GET"})
     * @Auth(P_CORVEE_MOD)
     */
    public function beheer()
    {
        return $this->render('maaltijden/vrijstelling/beheer_vrijstellingen.html.twig', ['vrijstellingen' => $this->corveeVrijstellingenRepository->findAll()]);
    }

    /**
     * @return VrijstellingForm
     * @Route("/corvee/vrijstellingen/nieuw", methods={"POST"})
     * @Auth(P_CORVEE_MOD)
     */
    public function nieuw()
    {
        return new VrijstellingForm($this->corveeVrijstellingenRepository->nieuw()); // fetches POST values itself
    }

    /**
     * @param Profiel $profiel
     * @return VrijstellingForm
     * @Route("/corvee/vrijstellingen/bewerk/{uid}", methods={"POST"})
     * @Auth(P_CORVEE_MOD)
     */
    public function bewerk(Profiel $profiel)
    {
        return new VrijstellingForm($this->corveeVrijstellingenRepository->getVrijstelling($profiel->uid)); // fetches POST values itself
    }

    /**
     * @param Profiel|null $profiel
     * @return VrijstellingForm|Response
     * @throws Throwable
     * @Route("/corvee/vrijstellingen/opslaan/{uid}", methods={"POST"}, defaults={"uid"=null})
     * @Auth(P_CORVEE_MOD)
     */
    public function opslaan(Profiel $profiel = null)
    {
        if ($profiel) {
            $view = $this->bewerk($profiel);
        } else {
            $view = $this->nieuw();
        }
        if ($view->validate()) {
            $values = $view->getModel();
            return $this->render('maaltijden/vrijstelling/beheer_vrijstelling_lijst.html.twig', [
                'vrijstelling' => $this->corveeVrijstellingenRepository->saveVrijstelling($values->profiel, $values->begin_datum, $values->eind_datum, $values->percentage)
            ]);
        }

        return $view;
    }

    /**
     * @param Profiel $profiel
     * @return PlainView
     * @Route("/corvee/vrijstellingen/verwijder/{uid}", methods={"POST"})
     * @Auth(P_CORVEE_MOD)
     */
    public function verwijder(Profiel $profiel)
    {
        $this->corveeVrijstellingenRepository->verwijderVrijstelling($profiel->uid);
        return new PlainView('<tr id="vrijstelling-row-' . $profiel->uid . '" class="remove"></tr>');
    }

}
