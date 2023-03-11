<?php

namespace CsrDelft\view;

use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class IsHetAlView implements View
{
	use ToHtmlResponse;

	/**
	 * Type of IsHetAlContent
	 * @var string
	 */
	private $model;
	/**
	 * True OR aantal dagen OR woordvandedag
	 * @var boolean|int|string
	 */
	private $ja;
	/**
	 * Aftellen voor deze typen IsHetAlContent
	 * @var array
	 */
	public static $aftellen = ['jarig', 'dies', 'lustrum'];
	/**
	 * Wist u dat'tjes
	 * @var array
	 */
	public static $wistudat = [
		'u de webstek geheel naar wens kan instellen?' => '/instellingen',
		'u de C.S.R.-agenda kan importeren met ICAL?' => '/profiel#agenda',
		'u het forum kan volgen met RSS?' => '/profiel#forum',
	];

	public function __construct(
		LidInstellingenRepository $lidInstellingenRepository,
		RequestStack $requestStack,
		AgendaRepository $agendaRepository,
		WoordVanDeDagRepository $woordVanDeDagRepository,
		$ishetal
	) {
		$session =
			$requestStack->getMainRequest() == null
				? new Session()
				: $requestStack->getMainRequest()->getSession();

		// Ongeveer de 1/4 van de tijd het lustrumwoord van de dag laten zien, alleen in de periode van 21-12-2021 tot 19-2-2022
		$differenceDays = floor(
			(strtotime(date('d-m-Y')) - strtotime('21-12-2021')) / 86400
		);
		if ($differenceDays >= 1 && $differenceDays <= 60 && rand(0, 100) < 25) {
			$this->model = 'wvdd';
			$woordVanDeDag = $woordVanDeDagRepository->find(intval($differenceDays));
			$this->ja = $woordVanDeDag
				? $woordVanDeDag->getWoord()
				: 'Woord van de dag niet gevonden';

			return;
		}
		$this->model = $ishetal;
		if ($this->model == 'willekeurig') {
			$opties = array_slice(
				$lidInstellingenRepository->getTypeOptions('zijbalk', 'ishetal'),
				2
			);
			$this->model = $opties[array_rand($opties)];
		}
		switch ($this->model) {
			case 'wist u dat':
			case 'foutmelding':
			// TODO: Weghalen dat sponsorkliks wordt laten zien

			case 'dies':
				$begin = strtotime('2023-02-13');
				$einde = strtotime('2023-02-24');
				$nu = strtotime(date('Y-m-d'));
				if ($nu > $einde) {
					$begin = strtotime('+1 year', $begin);
				}
				$dagen = round(($begin - $nu) / 86400);
				if ($dagen <= 0) {
					$this->ja = true;
				} else {
					$this->ja = $dagen;
				}
				break;

			case 'lustrum':
				$begin = strtotime('2021-06-16');
				$einde = strtotime('2022-06-16');
				$nu = strtotime(date('Y-m-d'));
				if ($nu > $einde) {
					$begin = strtotime('+5 year', $begin);
				}
				$dagen = round(($begin - $nu) / 86400);
				if ($dagen <= 0) {
					$this->ja = true;
				} else {
					$this->ja = $dagen;
				}
				break;

			case 'jarig':
				$this->ja = LoginService::getProfiel()->getJarigOver();
				break;

			case 'lunch':
				$this->ja = (date('Hi') > '1230' and date('Hi') < '1330');
				break;

			case 'weekend':
				$this->ja =
					(date('w') == 0 or
					date('w') > 5 or
					date('w') == 5 and date('Hi') >= '1700');
				break;

			case 'studeren':
				if ($session->has('studeren')) {
					$this->ja =
						(time() > $session->get('studeren') + 5 * 60 and date('w') != 0);
					$tijd = $session->get('studeren');
				} else {
					$this->ja = false;
					$tijd = time();
				}
				$session->set('studeren', $tijd);
				break;

			case 'kring':
				// Matcht 'kring 42', 'loremipsumkring', 'kringlezing', maar niet 'kringleidersinstructie'.
				$vandaag = $agendaRepository->zoekRegexAgenda('/kring(?: \d+|\b|lezing\b)/i');
				$this->ja = $vandaag instanceof AgendaItem;
				break;

			default:
				$vandaag = $agendaRepository->zoekWoordAgenda($this->model);
				if ($vandaag instanceof AgendaItem) {
					$this->ja = true;
					/*
					  $nu = time();
					  if ($this->model == 'borrel') {
					  $this->ja = $nu > $vandaag->getBeginMoment();
					  } else {
					  $this->ja = $nu > $vandaag->getBeginMoment() AND $nu < $vandaag->getEindMoment();
					  }
					 */
				} else {
					$this->ja = false;
				}
				break;
		}
	}

	public function getModel()
	{
		return $this->model;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getTitel()
	{
		return $this->model;
	}

	public function __toString()
	{
		$html = '';
		$html .=
			'<div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">';
		switch ($this->model) {
			case 'jarig':
				$html .= '<h4 class="h6 m-0">Ben ik al jarig?</h4>';
				break;

			case 'studeren':
				$html .= '<h4 class="h6 m-0">Moet ik alweer studeren?</h4>';
				break;

			case 'kring':
				$html .= '<h4 class="h6 m-0">Is er ' . $this->model . ' vanavond?</h4>';
				break;

			case 'lezing':
			case 'borrel':
				$html .=
					'<h4 class="h6 m-0">Is er een ' . $this->model . ' vanavond?</h4>';
				break;

			case 'wist u dat':
				$wistudat = array_rand(self::$wistudat);
				$html .=
					'<h4 class="h6 m-0">Wist u dat...<a href="' .
					self::$wistudat[$wistudat] .
					'" class="cursief">' .
					$wistudat .
					'</a></h4>';
				break;

			case 'wvdd':
				$html .=
					'<h4 class="h6 m-0">Het lustrumboekwoord van de dag is ' .
					$this->ja .
					' </h4>';
				break;

			default:
				$html .= '<h4 class="h6 m-0">Is het al ' . $this->model . '?</h4>';
				break;
		}

		if ($this->ja === true) {
			$html .= '<p class="text-uppercase fw-bolder fs-5 text-success">JA!</p>';
		} elseif ($this->ja === false) {
			$html .= '<p class="text-uppercase fw-bolder fs-5 text-danger">NEE.</p>';
		} elseif (in_array($this->model, self::$aftellen)) {
			$html .=
				'<p class="text-uppercase fw-bolder fs-5 text-danger">OVER ' .
				$this->ja .
				' ' .
				($this->ja == 1 ? 'DAG' : 'DAGEN') .
				'!</p>';
		} else {
			// wist u dat
		}

		$html .= '</div>';

		return $html;
	}
}
