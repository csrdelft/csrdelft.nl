<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\MijnSqli;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\AccountModel;

/**
 * VoorkeurCommissie
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Voorkeur houdt voorkeuren bij van leden voor commissies
 */
class VoorkeurCommissie {

	private $cid;
	private $naam;

	public function __construct($cid, $naam) {
		$this->cid = $cid;
		$this->naam = $naam;
	}

	public function getGeinteresseerde() {
		$db = MijnSqli::instance();
		$query = 'SELECT uid, voorkeur FROM voorkeurCommissie JOIN voorkeurVoorkeur ON voorkeurCommissie.id = voorkeurVoorkeur.cid WHERE zichtbaar = 1 
			AND (voorkeur = 2 OR voorkeur = 3) AND cid = ' . $this->cid . ' ORDER BY voorkeur DESC';
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$account = AccountModel::get($row['uid']);
			if ($account->getProfiel() && $account->getProfiel()->isLid()) {
				$gedaan = AccessModel::mag($account, 'commissie:' . $this->naam . ',commissie:' . $this->naam . ':ot');
				$res[$row['uid']] = array('voorkeur' => $row['voorkeur'], 'gedaan' => $gedaan);
			}
		}
		return $res;
	}

	public static function getCommissie($cid) {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE id = ' . $cid . '';
		$result = $db->select($query);
		$res = '';
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res = $row['naam'];
		}
		return new VoorkeurCommissie($cid, $res);
	}

	public static function getCommissies() {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE zichtbaar = 1 ';
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res[$row['id']] = $row['naam'];
		}
		return $res;
	}

	public function getNaam() {
		return $this->naam;
	}

}
