<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\StekPakket;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\StekPakketRepository;
use CsrDelft\view\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class StekPakketController {
	private $basispakketten = [
		[
			'titel' => 'Randlid',
			'usps' => [
				'Forum afgelopen week lezen',
				'Agenda komende 3 dagen',
				'Courant',
			],
			'euro' => 0,
			'centen' => 0,
			'niveau' => 1
		],
		[
			'titel' => 'Gewoon lid',
			'usps' => [
				'Forumhistorie lezen',
				'Volledige agenda',
				'Kringen & werkgroepen bekijken',
				'Lichtingen & verticalen bekijken',
				'Eigen gegevens aanpassen',
			],
			'euro' => 1,
			'centen' => 50,
			'niveau' => 2
		],
		[
			'titel' => 'Actief lid',
			'usps' => [
				'Forumberichten schrijven',
				'Maaltijdaanmeldingen',
				'In- en uitketzen in ketzers',
				'Commissies bekijken',
				'Documenten bekijken',
				'Wiki leesrechten',
			],
			'euro' => 4,
			'centen' => 0,
			'niveau' => 3
		],
		[
			'titel' => 'Harde kern',
			'usps' => [
				'Ketzers maken',
				'Onderverenigingen bekijken',
				'Documenten uploaden',
				'Bibliotheek',
				'Commissies op je profiel tonen',
				'Zien wie je profiel bezoekt',
				'Wiki schrijfrechten',
			],
			'euro' => 6,
			'centen' => 50,
			'niveau' => 4
		],
	];

	private $opties = [
		[
			'groep' => 'Forum',
			'opties' => [
				'f_week' => ['optie' => 'Forum afgelopen week lezen', 'vanaf' => 1, 'prijs' => 0, 'post' => 'f_lees'],
				'f_lees' => ['optie' => 'Forumhistorie lezen', 'vanaf' => 2, 'prijs' => 0.5, 'pre' => 'f_week'],
				'f_schrijf' => ['optie' => 'Forumberichten schrijven', 'vanaf' => 3, 'prijs' => 0.4],
			]
		],
		[
			'groep' => 'Agenda',
			'opties' => [
				'a_dagen' => ['optie' => 'Agenda komende 3 dagen bekijken', 'vanaf' => 1, 'prijs' => 0, 'post' => 'a_lees'],
				'a_lees' => ['optie' => 'Agenda toekomst bekijken', 'vanaf' => 2, 'prijs' => 0.2, 'pre' => 'a_dagen'],
				'a_maaltijd' => ['optie' => 'Maaltijdaanmeldingen', 'vanaf' => 3, 'prijs' => 0.2],
				'a_ketzen' => ['optie' => 'In- en uitketzen in ketzers', 'vanaf' => 3, 'prijs' => 0.3],
				'a_ketzer' => ['optie' => 'Ketzers maken', 'vanaf' => 4, 'prijs' => 0.3],
			]
		],
		[
			'groep' => 'Groepen',
			'opties' => [
				'g_kring' => ['optie' => 'Kringen & werkgroepen bekijken', 'vanaf' => 2, 'prijs' => 0.1],
				'g_lichting' => ['optie' => 'Lichtingen & verticalen bekijken', 'vanaf' => 2, 'prijs' => 0.2],
				'g_commissie' => ['optie' => 'Commissies bekijken', 'vanaf' => 3, 'prijs' => 0.3],
				'g_ondervereniging' => ['optie' => 'Onderverenigingen bekijken', 'vanaf' => 4, 'prijs' => 0.3],
			]
		],
		[
			'groep' => 'Communicatie',
			'opties' => [
				'c_courant' => ['optie' => 'Courant', 'vanaf' => 1, 'prijs' => 0],
				'c_kringdocumenten' => ['optie' => 'Kringdocumenten', 'vanaf' => 2, 'prijs' => 0.2, 'post' => 'c_document_lees'],
				'c_corvee' => ['optie' => 'Corveerooster', 'vanaf' => 2, 'prijs' => 0.1],
				'c_document_lees' => ['optie' => 'Documenten bekijken', 'vanaf' => 3, 'prijs' => 0.3, 'pre' => 'c_kringdocumenten'],
				'c_document_upload' => ['optie' => 'Documenten uploaden', 'vanaf' => 4, 'prijs' => 0.5],
				'c_bibliotheek' => ['optie' => 'Bibliotheek', 'vanaf' => 4, 'prijs' => 0.2],
			]
		],
		[
			'groep' => 'Ledengegevens',
			'opties' => [
				'l_aanpassen' => ['optie' => 'Eigen gegevens aanpassen', 'vanaf' => 2, 'prijs' => 0.2],
				'l_bekijken' => ['optie' => 'Ledengegevens bekijken', 'vanaf' => 3, 'prijs' => 0.5],
				'l_commissies' => ['optie' => 'Commissies op profiel tonen', 'vanaf' => 4, 'prijs' => 0.25],
				'l_bezoekers' => ['optie' => 'Zien wie je profiel bezoekt', 'vanaf' => 4, 'prijs' => 0.50],
			]
		],
		[
			'groep' => 'Wiki',
			'opties' => [
				'w_lees' => ['optie' => 'Wiki leesrechten', 'vanaf' => 3, 'prijs' => 0.5, 'post' => 'w_schrijf'],
				'w_schrijf' => ['optie' => 'Wiki schrijfrechten', 'vanaf' => 4, 'prijs' => 0.45, 'pre' => 'w_lees'],
			]
		],
	];

	public function kiezen(RouterInterface $router, StekPakketRepository $repo) {
		// Huidige keuze inladen
		$stekpakket = $repo->getStekPakketVoorLid(LoginModel::getProfiel());

		// Zet defaults
		foreach ($this->opties as $key => $groep) {
			foreach ($groep['opties'] as $optieKey => $optie) {
				$this->opties[$key]['opties'][$optieKey]['actief'] = $stekpakket && in_array($optieKey, $stekpakket->opties);
			}
		}

		// Laad Vue app.
		return view('stekpakket', [
			'basispakketten' => json_encode($this->basispakketten),
			'basispakket' => $stekpakket ? $stekpakket->basispakket : '',
			'opties' => json_encode($this->opties),
			'opslaan' => $router->generate('stekpakket-opslaan'),
		]);
	}

	public function opslaan(Request $request, StekPakketRepository $repo, EntityManagerInterface $em) {
		// Valideren
		foreach ($this->basispakketten as $pakket) {
			if ($pakket['titel'] === $request->request->get('basispakket')) {
				$basispakket = $pakket['titel'];
			}
		}

		if (!isset($basispakket)) {
			throw new CsrGebruikerException("Selecteer een basispakket");
		}

		// Opties
		$opties = $request->request->get('opties', []);
		if (!is_array($opties)) {
			$opties = [];
		}

		$prijs = 0;
		$geselecteerdeOpties = [];
		foreach ($this->opties as $groep) {
			foreach ($groep['opties'] as $key => $optie) {
				if (in_array($key, $opties)) {
					$prijs += $optie['prijs'];
					$geselecteerdeOpties[] = $key;
				}
			}
		}

		// Oude verwijderen
		$uid = LoginModel::getUid();
		$old = $repo->findOneBy(['uid' => $uid]);
		if ($old) {
			$em->remove($old);
			$em->flush();
		}

		// Nieuwe invoegen
		$stekpakket = new StekPakket();
		$stekpakket->setProfiel(LoginModel::getProfiel());
		$stekpakket->basispakket = $basispakket;
		$stekpakket->opties = $geselecteerdeOpties;
		$stekpakket->prijs = $prijs;
		$stekpakket->setTimestamp();
		$em->persist($stekpakket);
		$em->flush();

		return new JsonResponse(['success' => true]);
	}
}
