<?php

namespace CsrDelft\view;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\agenda\AgendaRepository;

class IsHetAlView implements View {
	/**
	 * Type of IsHetAlContent
	 * @var string
	 */
	private $model;
	/**
	 * True OR aantal dagen
	 * @var boolean|int
	 */
	private $ja;
	/**
	 * Aftellen voor deze typen IsHetAlContent
	 * @var array
	 */
	public static $aftellen = array('jarig', 'dies');
	/**
	 * Wist u dat'tjes
	 * @var array
	 */
	public static $wistudat = array(
		'u de webstek geheel naar wens kan instellen?' => '/instellingen',
		'u de C.S.R.-agenda kan importeren met ICAL?' => '/profiel#agenda',
		'u het forum kan volgen met RSS?' => '/profiel#forum'
	);

	public function __construct($ishetal) {
		$this->model = $ishetal;
		if ($this->model == 'willekeurig') {
			$opties = array_slice(LidInstellingenModel::instance()->getTypeOptions('zijbalk', 'ishetal'), 2);
			$this->model = $opties[array_rand($opties)];
		}
		switch ($this->model) {
			case 'wist u dat':
			case 'foutmelding':
			case 'sponsorkliks':
				$this->ja = null;
				break;

			case 'dies' :
				$begin = strtotime('2020-02-11');
				$einde = strtotime('2020-02-22');
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

			case 'jarig':
				$this->ja = LoginModel::getProfiel()->getJarigOver();
				break;

			case 'lunch':
				$this->ja = (date('Hi') > '1230' AND date('Hi') < '1330');
				break;

			case 'weekend':
				$this->ja = (date('w') == 0 OR date('w') > 5 OR (date('w') == 5 AND date('Hi') >= '1700'));
				break;

			case 'studeren':
				if (isset($_COOKIE['studeren'])) {
					$this->ja = (time() > ($_COOKIE['studeren'] + 5 * 60) AND date('w') != 0);
					$tijd = $_COOKIE['studeren'];
				} else {
					$this->ja = false;
					$tijd = time();
				}
				setcookie('studeren', $tijd, time() + 30 * 60, '/', CSR_DOMAIN, FORCE_HTTPS, true);
				break;

			default:
				$agendaRepository = ContainerFacade::getContainer()->get(AgendaRepository::class);
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

	public function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->model;
	}

	public function view() {
		echo '<div class="ishetal">';
		switch ($this->model) {
			case 'sponsorkliks':
				echo '<iframe src="https://banner.sponsorkliks.com/skinfo.php?&background-color=F5F5F5&text-color=000000&header-background-color=F5F5F5&header-text-color=F5F5F5&odd-row=FFFFFF&even-row=09494a&odd-row-text=09494a&even-row-text=ffffff&type=financial&club_id=3605&width=193" frameborder="0" referrerpolicy="no-referrer" class="sponsorkliks-zijbalk"></iframe>';
				break;

			case 'jarig':
				echo 'Ben ik al jarig?';
				break;

			case 'studeren':
				echo 'Moet ik alweer studeren?';
				break;

			case 'kring':
				echo 'Is er ' . $this->model . ' vanavond?';
				break;

			case 'lezing':
			case 'borrel':
				echo 'Is er een ' . $this->model . ' vanavond?';
				break;

			case 'foutmelding':
				if (file_exists(VAR_PATH . 'foutmelding.last')) {
					echo '<div class="ja">' . reldate(date('c', filemtime(VAR_PATH . 'foutmelding.last'))) . '</div><div>sinds de laatste foutmelding!</div>';
				} else {
					echo '<div class="nee">Geen foutmelding in het systeem.</div>';
				}
				break;

			case 'wist u dat':
				$wistudat = array_rand(self::$wistudat);
				echo '<div class="ja">Wist u dat...</div><a href="' . self::$wistudat[$wistudat] . '" class="cursief">' . $wistudat . '</a>';
				break;

			default:
				echo 'Is het al ' . $this->model . '?';
				break;
		}

		if ($this->ja === true) {
			echo '<div class="ja">JA!</div>';
		} elseif ($this->ja === false) {
			echo '<div class="nee">NEE.</div>';
		} elseif (in_array($this->model, self::$aftellen)) {
			echo '<div class="nee">OVER ' . $this->ja . ' ' . ($this->ja == 1 ? 'DAG' : 'DAGEN') . '!</div>';
		} else {
			// wist u dat
		}
		echo '</div>';
	}

}
