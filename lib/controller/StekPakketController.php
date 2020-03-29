<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\Lichting;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\ledenmemory\LedenMemoryScoreForm;
use CsrDelft\view\ledenmemory\LedenMemoryScoreResponse;

class StekPakketController {
	private $basispakketten = [
		[
			'titel' => 'Randlid',
			'usps' => [
				'Forum afgelopen week lezen',
				'Agenda komende 3 dagen',
				'Courant',
				'2 CiviSaldo-transacties',
			],
			'euro' => 0,
			'centen' => 0,
			'onderdelen' => [],
		],
		[
			'titel' => 'Gewoon lid',
			'usps' => [
				'Forumhistorie lezen',
				'Volledige agenda',
				'Kringen & werkgroepen bekijken',
				'Lichtingen & verticalen bekijken',
				'Eigen gegevens aanpassen',
				'5 CiviSaldo-transacties',
			],
			'euro' => 1,
			'centen' => 50,
			'onderdelen' => [],
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
				'20 CiviSaldo-transacties',
			],
			'euro' => 4,
			'centen' => 0,
			'onderdelen' => [],
		],
		[
			'titel' => 'Harde kern',
			'usps' => [
				'Ketzers maken',
				'Onderverenigingen bekijken',
				'Documenten uploaden',
				'Bibliotheek',
				'Commissies op je profiel tonen',
				'Wiki schrijfrechten',
				'100 CiviSaldo-transacties',
				'Zien wie je profiel heeft bekeken'
			],
			'euro' => 7,
			'centen' => 50,
			'onderdelen' => [],
		],
	];

	public function kiezen() {
		// Haal huidige keuzes op

		// Laad Vue app.
		return view('stekpakket', [
			'basispakketten' => json_encode($this->basispakketten)
		]);
	}
}
