<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\LDAP;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\ProfielService;
use CsrDelft\model\Roodschopper;
use CsrDelft\model\SavedQuery;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\Persistence\OrmMemcache;
use CsrDelft\view\AdminsView;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\NovietenView;
use CsrDelft\view\PlainView;
use CsrDelft\view\roodschopper\RoodschopperForm;
use CsrDelft\view\SavedQueryContent;
use CsrDelft\view\StatsView;
use CsrDelft\view\Streeplijstcontent;
use CsrDelft\view\VerticaleLijstenView;

/**
 * Deze controller bevat een aantal beheertools die niet direct onder een andere controller geschaard kunnen worden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class ToolsController extends AclController {
	public function __construct($query) {
		parent::__construct($query, null);

		if ($this->getMethod() == 'POST') {
			$this->acl = [
				'bbcode' => P_PUBLIC,
				'dragobject' => P_LOGGED_IN,
				'naamlink' => P_OUDLEDEN_READ,
				'roodschopper' => P_FISCAAT_MOD,
			];
		} else {
			$this->acl = [
				'stats' => P_ADMIN,
				'query' => P_LEDEN_READ,
				'bbcode' => P_PUBLIC,
				'phpinfo' => P_ADMIN,
				'admins' => P_LEDEN_READ,
				'novieten' => P_ADMIN . ',commissie:NovCie',
				'naamsuggesties' => P_OUDLEDEN_READ,
				'naamlink' => P_OUDLEDEN_READ,
				'streeplijst' => P_OUDLEDEN_READ,
				'verticalelijsten' => P_ADMIN,
				'roodschopper' => P_FISCAAT_MOD,
				'syncldap' => P_PUBLIC,
			];
		}
	}

	public function performAction(array $args = array()) {
		$this->action = $this->getParam(2);
		$this->view = parent::performAction($args);
	}

	public function streeplijst() {
		$body = new Streeplijstcontent();

		# yuck
		if (isset($_GET['iframe'])) {
			return new PlainView($body->getHtml());
		} else {
			return view('default', ['content' => $body]);
		}
	}

	public function stats() {
		return view('default', ['content' => new StatsView()]);
	}

	public function verticalelijsten() {
		return view('default', ['content' => new VerticaleLijstenView()]);
	}

	public function roodschopper() {
		if ($this->hasParam('verzenden')) {
			return view('roodschopper.roodschopper', [
				'verzenden' => true,
				'aantal' => $this->getParam('aantal'),
			]);
		}

		$roodschopper = Roodschopper::getDefaults();
		$roodschopperForm = new RoodschopperForm($roodschopper);

		if ($roodschopperForm->isPosted() && $roodschopperForm->validate() && $roodschopper->verzenden) {
			$roodschopper->sendMails();
			// Voorkom dubbele submit
			redirect('/tools/roodschopper?verzenden=true&aantal=' . count($roodschopper->getSaldi()));
		} else {
			$roodschopper->generateMails();
		}

		return view('roodschopper.roodschopper', [
			'verzenden' => false,
			'form' => $roodschopperForm,
			'saldi' => $roodschopper->getSaldi(),
		]);
	}

	public function syncldap() {
		if (DEBUG OR LoginModel::mag(P_ADMIN) OR LoginModel::instance()->isSued()) {
			$ldap = new LDAP();
			$model = ProfielModel::instance();

			foreach ($model->find() as $profiel) {
				$model->save_ldap($profiel, $ldap);
			}

			$ldap->disconnect();

			return new PlainView('done');
		}

		throw new CsrToegangException();
	}

	public function phpinfo() {
		ob_start();
		phpinfo();
		return new PlainView(ob_get_clean());
	}

	public function admins() {
		return view('default', ['content' => new AdminsView()]);
	}

	public function novieten() {
		$novieten = Database::instance()->sqlSelect(['*'], 'profielen', 'status = ?', ['S_NOVIET']);

		return view('default', ['content' => new NovietenView($novieten)]);
	}

	public function dragobject() {
		$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
		$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

		$_SESSION['dragobject'][$id] = $coords;
	}

	public function naamlink() {
//is er een uid gegeven?
		$given = 'uid';
		if (isset($_GET['uid'])) {
			$string = urldecode($_GET['uid']);
		} elseif (isset($_POST['uid'])) {
			$string = $_POST['uid'];

//is er een naam gegeven?
		} elseif (isset($_GET['naam'])) {
			$string = urldecode($_GET['naam']);
			$given = 'naam';
		} elseif (isset($_POST['naam'])) {
			$string = $_POST['naam'];
			$given = 'naam';
		} else { //geen input
			throw new CsrGebruikerException('Geen naam invoer in naamlink');
		}

//welke subset van leden?
		$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$toegestanezoekfilters = ['leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies'];
		if (isset($_GET['zoekin']) && in_array($_GET['zoekin'], $toegestanezoekfilters)) {
			$zoekin = $_GET['zoekin'];
		}

		function uid2naam($uid) {
			$naam = ProfielModel::getLink($uid, 'civitas');
			if ($naam !== false) {
				return $naam;
			} else {
				return 'Lid[' . htmlspecialchars($uid) . '] &notin; db.';
			}
		}

//zoekt uid op en returnt met uid2naam weer de naam
		function zoekNaam($naam, $zoekin) {
			$namen = ProfielService::instance()->zoekLeden($naam, 'naam', 'alle', 'achternaam', $zoekin);
			if (!empty($namen)) {
				if (count($namen) === 1) {
					return $namen[0]->getLink('civitas');
				} else {
					return 'Meerdere leden mogelijk';
				}
			}
			return 'Geen lid gevonden';
		}

		if ($given == 'uid') {
			if (AccountModel::isValidUid($string)) {
				return new PlainView(uid2naam($string));
			} else {
				$uids = explode(',', $string);
				foreach ($uids as $uid) {
					return new PlainView(uid2naam($uid));
				}
			}
		} elseif ($given == 'naam') {
			return new PlainView(zoekNaam($string, $zoekin));
		}
	}

	public function naamsuggesties() {
		//welke subset van leden?
		$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$toegestanezoekfilters = array('leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies');
		if (isset($_GET['zoekin']) && in_array($_GET['zoekin'], $toegestanezoekfilters)) {
			$zoekin = $_GET['zoekin'];
		}
		if (isset($_GET['zoekin']) && $_GET['zoekin'] === 'voorkeur') {
			$zoekin = LidInstellingenModel::get('forum', 'lidSuggesties');
		}

		$query = '';
		if (isset($_GET['q'])) {
			$query = $_GET['q'];
		}
		$limiet = 5;
		if (isset($_GET['limit'])) {
			$limiet = (int)$_GET['limit'];
		}

		$toegestaneNaamVormen = ['user', 'volledig', 'streeplijst', 'voorletters', 'bijnaam', 'Duckstad', 'civitas', 'aaidrom'];
		$vorm = 'volledig';
		if (isset($_GET['vorm']) && in_array($_GET['vorm'], $toegestaneNaamVormen)) {
			$vorm = $_GET['vorm'];
		}

		$profielen = ProfielService::instance()->zoekLeden($query, 'naam', 'alle', 'achternaam', $zoekin, $limiet);

		$result = array();
		foreach ($profielen as $profiel) {
			$tussenvoegsel = ($profiel->tussenvoegsel != '') ? $profiel->tussenvoegsel . ' ' : '';
			$fullname = $profiel->voornaam . ' ' . $tussenvoegsel . $profiel->achternaam;

			$result[] = array(
				'url' => '/profiel/' . $profiel->uid,
				'label' => $profiel->uid,
				'value' => $profiel->getNaam($vorm)
			);
		}
		/*
      if (empty($result)) {
      $result[] = array(
      'url' => '/ledenlijst?status=LEDEN|OUDLEDEN&q=' . urlencode($query),
      'label' => 'Zoeken in <span class="dikgedrukt">leden & oudleden</span>',
      'value' => htmlspecialchars($query)
      );
      }
     */

		return new JsonResponse($result);
	}

	public function memcachestats() {
		if (DEBUG || LoginModel::mag(P_ADMIN) || LoginModel::instance()->isSued()) {
			ob_start();

			echo getMelding();
			echo '<h1>MemCache statistieken</h1>';
			debugprint(OrmMemcache::instance()->getCache()->getStats());

			return new PlainView(ob_get_clean());
		}

		throw new CsrToegangException();
	}

	public function query() {
		if (isset($_GET['id']) && (int)$_GET['id'] == $_GET['id']) {
			$id = (int)$_GET['id'];
			$savedquery = new SavedQuery($id);
		} else {
			$savedquery = null;
		}

		return view('default', [
			'content' => new SavedQueryContent($savedquery),
		]);
	}

	public function bbcode() {
		$inputJSON = file_get_contents('php://input');
		$input = json_decode($inputJSON, TRUE);

		if (isset($_POST['data'])) {
			$string = urldecode($_POST['data']);
		} elseif (isset($_GET['data'])) {
			$string = $_GET['data'];
		} elseif (isset($input['data'])) {
			$string = urldecode($input['data']);
		} else {
			$string = 'b0rkb0rkb0rk: geen invoer in htdocs/tools/bbcode';
		}

		$string = trim($string);

		if (isset($_POST['mail']) || isset($input['mail'])) {
			return new PlainView(CsrBB::parseMail($string));
		} else {
			return new PlainView(CsrBB::parse($string));
		}
	}
}
