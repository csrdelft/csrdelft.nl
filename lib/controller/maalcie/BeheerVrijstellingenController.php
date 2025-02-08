<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\view\maalcie\forms\VrijstellingForm;
use CsrDelft\view\PlainView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerVrijstellingenController extends AbstractController
{
	public function __construct(
		private readonly CorveeVrijstellingenRepository $corveeVrijstellingenRepository
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/vrijstellingen', methods: ['GET'])]
	public function beheer()
	{
		return $this->render(
			'maaltijden/vrijstelling/beheer_vrijstellingen.html.twig',
			['vrijstellingen' => $this->corveeVrijstellingenRepository->findAll()]
		);
	}

	/**
	 * @return VrijstellingForm
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/vrijstellingen/nieuw', methods: ['POST'])]
	public function nieuw()
	{
		return new VrijstellingForm($this->corveeVrijstellingenRepository->nieuw()); // fetches POST values itself
	}

	/**
	 * @param Profiel $profiel
	 * @return VrijstellingForm
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/vrijstellingen/bewerk/{uid}', methods: ['POST'])]
	public function bewerk(Profiel $profiel)
	{
		return new VrijstellingForm(
			$this->corveeVrijstellingenRepository->getVrijstelling($profiel->uid)
		); // fetches POST values itself
	}

	/**
	 * @param Profiel|null $profiel
	 * @return VrijstellingForm|Response
	 * @throws Throwable
	 * @Auth(P_CORVEE_MOD)
	 */
	#[
		Route(
			path: '/corvee/vrijstellingen/opslaan/{uid}',
			methods: ['POST'],
			defaults: ['uid' => null]
		)
	]
	public function opslaan(Profiel $profiel = null)
	{
		if ($profiel) {
			$view = $this->bewerk($profiel);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			$values = $view->getModel();
			return $this->render(
				'maaltijden/vrijstelling/beheer_vrijstelling_lijst.html.twig',
				[
					'vrijstelling' => $this->corveeVrijstellingenRepository->saveVrijstelling(
						$values->profiel,
						$values->begin_datum,
						$values->eind_datum,
						$values->percentage
					),
				]
			);
		}

		return $view;
	}

	/**
	 * @param Profiel $profiel
	 * @return PlainView
	 * @Auth(P_CORVEE_MOD)
	 */
	#[Route(path: '/corvee/vrijstellingen/verwijder/{uid}', methods: ['POST'])]
	public function verwijder(Profiel $profiel)
	{
		$this->corveeVrijstellingenRepository->verwijderVrijstelling($profiel->uid);
		return new PlainView(
			'<tr id="vrijstelling-row-' . $profiel->uid . '" class="remove"></tr>'
		);
	}
}
