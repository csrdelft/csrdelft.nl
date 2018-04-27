<?php

namespace CsrDelft\model\mededelingen;

use CsrDelft\model\entity\mededelingen\Mededeling;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;

/**
 * MededelingenModel.class.php
 *
 * @author Maarten Somhorst
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class MededelingenModel extends PersistenceModel {

	const ORM = Mededeling::class;
	const DEFAULT_PRIORITEIT = 255;

	/**
	 * Valideer een gegeven mededeling.
	 *
	 * @param Mededeling $mededeling
	 * @return string Foutmelding
	 */
	public function validate($mededeling) {
		$errors = '';

		if (strlen($mededeling->titel) < 2) {
			$errors .= 'Het veld <span class="dikgedrukt">Titel</span> moet minstens 2 tekens bevatten.<br/>';
		}

		if (strlen($mededeling->tekst) < 5) {
			$errors .= 'Het veld <span class="dikgedrukt">Tekst</span> moet minstens 5 tekens bevatten.<br/>';
		}

		if ($mededeling->vervaltijd) {
			$vervaltijd = strtotime($mededeling->vervaltijd);
			$tijd = strtotime($mededeling->datum);
			if ($vervaltijd === false || !isGeldigeDatum($mededeling->vervaltijd)) {
				$errors .= 'Vervaltijd is ongeldig.<br/>';
			} elseif ($vervaltijd <= $tijd) {
				$errors .= 'Vervaltijd moet groter zijn dan de huidige tijd.<br/>';
			}
		}

		if (!$this->isModerator()) {
			if (isset($mededeling->prioriteit) && !array_search($mededeling->prioriteit, array_keys($this->getPrioriteiten()))) {
				$mededeling->prioriteit = MededelingenModel::DEFAULT_PRIORITEIT;
			}
		}

		if (!$mededeling->getCategorie() || !$mededeling->getCategorie()->magUitbreiden()) {
			$errors .= 'De categorie is ongeldig.';
		}

		return $errors;
	}

	/**
	 * @param string[] $image
	 * @param Mededeling $mededeling
	 *
	 * @return string
	 */
	public function savePlaatje($image, $mededeling) {
		$img_errors = '';
		if ($image['error'] == UPLOAD_ERR_OK) {
			$image_info = getimagesize($image['tmp_name']);
			if ($image_info[0] == 0 || $image_info[1] == 0) {
				$img_errors .= 'Het is niet gelukt om de resolutie van het plaatje te bepalen.<br/>';
			} else {
				$image_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
				$image_path = PHOTOS_PATH . 'mededelingen/' . substr(md5(time()), 0, 8) . '.' . $image_extension;
				$i = 1;
				while (file_exists($image_path)) {
					// Vind een unieke hash
					$image_path = PHOTOS_PATH . 'mededelingen/' . substr(md5(time() + $i++), 0, 8) . '.' . $image_extension;
				}
				if (move_uploaded_file($image['tmp_name'], $image_path) === false) {
					$img_errors .= 'Plaatje verplaatsen is mislukt.<br/>';
				} else {
					$mededeling->plaatje = pathinfo($image_path, PATHINFO_BASENAME);
					chmod($image_path, 0644);
				}

			}
		}
		return $img_errors;
	}

	/**
	 * @param int $aantal
	 * @param string $doelgroep
	 *
	 * @return Mededeling[]
	 */
	public function getTopmost($aantal, $doelgroep = null) {
		$topmost = array();
		if (!is_numeric($aantal) OR $aantal <= 0) {
			return $topmost;
		}

		// Doelgroep bepalen en checken.
		$doelgroepClause = " AND ";
		switch ($doelgroep) {
			case 'nietleden':
				$doelgroepClause .= "doelgroep='iedereen'";
				break;
			case 'leden': // De gebruiker mag alleen leden-berichten zien als hij daar rechten toe heeft.
				$doelgroepClause .= LoginModel::mag('P_LEDEN_READ') ? "doelgroep!='oudleden'" : "doelgroep='iedereen'"; // Let op de != en =
				break;
			case 'oudleden': // De gebruiker mag alleen oudlid-berichten zien als hij oudlid of moderator is.
				if (LoginModel::mag('status:oudlid') OR LoginModel::mag('P_NEWS_MOD')) {
					$doelgroepClause .= "doelgroep!='leden'";
				} elseif (LoginModel::mag('P_LEDEN_READ')) { // Anders mag een normaal lid ledenberichten zien én de berichten voor iedereen.
					$doelgroepClause .= "doelgroep!='oudleden'";
				} else { // Anders mag een niet-lid alleen de berichten zien die voor iedereen bestemd zijn.
					$doelgroepClause .= "doelgroep='iedereen'";
				}
				break;
			default:
				// Indien $doelgroep niet is opgegeven of ongeldig is, kijken we wat het beste past bij de huidige gebruiker.
				if (LoginModel::mag('status:oudlid')) {
					$doelgroepClause .= "doelgroep!='leden'";
				} elseif (LoginModel::mag('P_LEDEN_READ')) {
					$doelgroepClause .= "doelgroep!='oudleden'";
				} else {
					$doelgroepClause .= "doelgroep='iedereen'";
				}
				break;
		}

		return $this->find(
			"(vervaltijd IS NULL OR vervaltijd > ?) AND zichtbaarheid='zichtbaar'" . $doelgroepClause,
			array(getDateTime()),
			null,
			'prioriteit ASC, datum DESC',
			$aantal)->fetchAll();
	}

	/**
	 * @param int $pagina
	 * @param int $aantal
	 * @param bool $prullenbak
	 *
	 * @return Mededeling[]
	 */
	public function getLijstVanPagina($pagina = 1, $aantal, $prullenbak = false) {
		// Prullenbak checken.
		if ($prullenbak AND !LoginModel::mag('P_NEWS_MOD')) {
			$prullenbak = false;
		}

		// Initialisaties.
		$mededelingen = array();

		$resultaat = $this->find(
			MededelingenModel::getClauses($prullenbak),
			array(),
			null,
			'datum DESC',
			$aantal, (($pagina - 1) * $aantal));

		foreach ($resultaat as $mededeling) {
			$groepeerstring = strftime('%B %Y', strtotime($mededeling->datum)); // Maand voluit en jaar.
			if (!isset($mededelingen[$groepeerstring]))
				$mededelingen[$groepeerstring] = array();
			$mededelingen[$groepeerstring][] = $mededeling;
		}

		return $mededelingen;
	}

	/**
	 * @return Mededeling[]
	 */
	public function getLijstWachtGoedkeuring() {
		$mededelingen = array();
		// Moderators of niet-ingelogden hebben geen berichten die wachten op goedkeuring.
		if (LoginModel::mag('P_NEWS_MOD') OR !LoginModel::mag('P_LEDEN_READ'))
			return $mededelingen;

		$resultaat = $this->find('uid=? AND zichtbaarheid="wacht_goedkeuring"',
			array(LoginModel::getUid()),
			'datum DESC');

		foreach ($resultaat as $mededeling) {
			$datum = date_create($mededeling->datum);
			$groepeerstring = $datum->format('F Y'); // Maand voluit en jaar.
			if (!isset($mededelingen[$groepeerstring]))
				$mededelingen[$groepeerstring] = array();
			$mededelingen[$groepeerstring][] = $mededeling;
		}
		return $mededelingen;
	}

	/**
	 * @param bool $prullenbak
	 *
	 * @return int
	 */
	public function getAantal($prullenbak) {
		return $this->count(MededelingenModel::getClauses($prullenbak));
	}

	/**
	 * @param Mededeling $mededeling
	 * @param bool $prullenbak
	 *
	 * @return int
	 */
	public function getPaginaNummer($mededeling, $prullenbak) {
		$clauses = MededelingenModel::getClauses($prullenbak);

		$positie = $this->count(
			$clauses . ' AND datum >= ?',
			array($mededeling->datum));

		$paginaNummer = (int)ceil($positie / LidInstellingenModel::get('mededelingen', 'aantalPerPagina'));
		$paginaNummer = $paginaNummer >= 1 ? $paginaNummer : 1; // Het moet natuurlijk wel groter dan 0 zijn.
		return $paginaNummer;
	}

	/**
	 * @param int $aantal
	 *
	 * @return \PDOStatement|Mededeling[]
	 */
	public static function getLaatsteMededelingen($aantal) {
		return static::instance()->find(MededelingenModel::getClauses(false),
			array(getDateTime()),
			null,
			'datum DESC, id DESC',
			$aantal
		);
	}

	/**
	 * @return string[]
	 */
	public function getPrioriteiten() {
		$prioriteiten = array();
		$prioriteiten[255] = 'geen';
		for ($i = 1; $i <= 6; $i++) {
			$prioriteiten[$i] = 'Prioriteit ' . $i;
		}
		return $prioriteiten;
	}

	/**
	 * @return string[]
	 */
	public function getDoelgroepen() {
		return ['iedereen', '(oud)leden', 'leden'];
	}

	/**
	 * @return bool
	 */
	public static function isModerator() {
		return LoginModel::mag('P_NEWS_MOD');
	}

	/**
	 * @return bool
	 */
	public static function isOudlid() {
		return LoginModel::mag('status:oudlid');
	}

	/**
	 * @return bool
	 */
	public static function magPriveLezen() {
		return LoginModel::mag('P_LEDEN_READ');
	}

	/**
	 * @return bool
	 */
	public static function magToevoegen() {
		return LoginModel::mag('P_NEWS_POST');
	}

	/**
	 * @param bool $prullenbak
	 *
	 * @return string
	 */
	public static function getClauses($prullenbak) {
		// Verval clause.
		$vervalClause = "(vervaltijd IS NULL OR vervaltijd > '" . getDateTime() . "')";
		if ($prullenbak) {
			$vervalClause = "(vervaltijd IS NOT NULL AND vervaltijd <= '" . getDateTime() . "')";
		}
		// Operator tussen de verval clause en verborgen clause.
		$operator = "AND";
		if ($prullenbak) {
			$operator = "OR";
		}
		// Verborgen clause.
		$verborgenClause = "zichtbaarheid='zichtbaar'";
		if ($prullenbak) {
			$verborgenClause = "(zichtbaarheid='verwijderd' OR zichtbaarheid='onzichtbaar')";
		} elseif (LoginModel::mag('P_NEWS_MOD')) { // Als de gebruiker moderator is, mag hij ook wacht_goedkeuring-berichten zien.
			$verborgenClause = "(zichtbaarheid='zichtbaar' OR zichtbaarheid='wacht_goedkeuring')";
		}
		// Doelgroep clause.
		$doelgroepClause = "";
		if (!LoginModel::mag('P_LEDEN_READ')) {
			$doelgroepClause = " AND doelgroep='iedereen'";
		} elseif (LoginModel::mag('status:oudlid')) {
			$doelgroepClause = " AND doelgroep!='leden'";
		}

		return '(' . $vervalClause . ' ' . $operator . ' ' . $verborgenClause . ')' . $doelgroepClause;
	}
}
