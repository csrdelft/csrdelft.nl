<?php

class IsHetAlContent implements View {

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
	public static $aftellen = array('jarig', 'dies', 'happie');
	/**
	 * Wist u dat'tjes
	 * @var array
	 */
	public static $wistudat = array(
		'u deze zijbalk geheel naar wens kan ingerichten?'	 => '/instellingen#tabs-zijbalk',
		'u ongelezen draadjes als gelezen kan weergeven?'	 => '/instellingen#tabs-forum',
		'u een eigen minion op de stek kan krijgen?'		 => '/instellingen#tabs-layout',
		'u de C.S.R.-agenda kan importeren met ICAL?'		 => '/agenda#ICAL'
	);

	public function __construct($ishetal) {
		$this->model = $ishetal;
		if ($this->model == 'willekeurig') {
			$opties = array_slice(LidInstellingen::instance()->getTypeOptions('zijbalk', 'ishetal'), 2);
			$this->model = $opties[array_rand($opties)];
		}
		switch ($this->model) {
			case 'dies' :
				$begin = strtotime('2014-02-11');
				$einde = strtotime('2014-02-21');
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
			case 'happie' :
				$begin = strtotime('2014-11-19');
				$einde = strtotime('2014-12-19');
				$nu = strtotime(date('Y-m-d'));
				$dagen = round(($begin - $nu) / 86400);
				if ($dagen <= 0) {
					$this->ja = true;
				} else {
					$this->ja = $dagen;
				}
				break;
			case 'jarig': $this->ja = LoginModel::instance()->getLid()->getJarigOver();
				break;
			case 'lunch': $this->ja = (date('Hi') > '1230' AND date('Hi') < '1330');
				break;
			case 'weekend': $this->ja = (date('w') == 0 OR date('w') > 5 OR ( date('w') == 5 AND date('Hi') > '1700'));
				break;
			case 'studeren':
				if (isset($_COOKIE['studeren'])) {
					$this->ja = (time() > ($_COOKIE['studeren'] + 5 * 60) AND date('w') != 0);
					$tijd = $_COOKIE['studeren'];
				} else {
					$this->ja = false;
					$tijd = time();
				}
				setcookie('studeren', $tijd, time() + 30 * 60);
				break;
			case 'wist u dat':
				$this->ja = null;
				break;
			default:
				require_once 'model/AgendaModel.class.php';
				$vandaag = AgendaModel::instance()->zoekWoordAgenda($this->model);
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
			case 'happie':
				echo 'Is <a href="http://www.facebook.com/HappieDelft" class="understreept">Happietaria</a> al geopend?';
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
			echo '<div class="nee">OVER ' . $this->ja . ' DAGEN!</div>';
		} else {
			// wist u dat
		}
		echo '</div>';
	}

}
