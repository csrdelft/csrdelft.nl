<?php

namespace CsrDelft\model;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\DependencyManager;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2018
 */
class ProfielService extends DependencyManager {
	/**
	 * @param string $zoekterm
	 * @param string $zoekveld
	 * @param string $verticale
	 * @param string $sort
	 * @param string $zoekstatus
	 * @param int $limiet
	 * @return Profiel[]
	 */
	public function zoekLeden(string $zoekterm, string $zoekveld, string $verticale, string $sort, $zoekstatus = '', int $limiet = 0) {
		$containsZonderSpatiesZoekterm = sql_contains(str_replace(' ', '', $zoekterm));
		$zoekfilterparams = [];
		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if ($zoekveld == 'naam' AND !preg_match('/^\d{2}$/', $zoekterm)) {
			if (preg_match('/ /', trim($zoekterm))) {
				$zoekdelen = explode(' ', $zoekterm);
				$iZoekdelen = count($zoekdelen);
				if ($iZoekdelen == 2) {
					$zoekfilterparams[':voornaam'] = sql_contains($zoekdelen[0]);
					$zoekfilterparams[':achternaam'] = sql_contains($zoekdelen[1]);
					$zoekfilter = "( voornaam LIKE :voornaam AND achternaam LIKE :achternaam ) OR";
					$zoekfilter .= "( voornaam LIKE :zoekterm OR achternaam LIKE :containsZoekterm OR
                                    nickname LIKE :containsZoekterm OR uid LIKE :containsZoekterm )";
					$zoekfilterparams[':zoekterm']=  $zoekterm;
					$zoekfilterparams[':containsZoekterm']=  sql_contains($zoekterm);
				} else {
					$zoekfilterparams[':voornaam'] = sql_contains($zoekdelen[0]);
					$zoekfilterparams[':achternaam'] = sql_contains($zoekdelen[$iZoekdelen - 1]);

					$zoekfilter = "( voornaam LIKE :voornaam AND achternaam LIKE :achternaam )";
				}
			} else {
				$zoekfilter = "
					voornaam LIKE :containsZoekterm OR achternaam LIKE :containsZoekterm OR
					nickname LIKE :containsZoekterm OR uid LIKE :containsZoekterm";
				$zoekfilterparams[':containsZoekterm']= sql_contains($zoekterm);
			}

			$zoekfilterparams[':naam'] = sql_contains($zoekterm);
			$zoekfilter .= " OR ( CONCAT(voornaam, \" \", tussenvoegsel, \" \", achternaam) LIKE :naam ) OR";
			$zoekfilter .= "( CONCAT(voornaam, \" \", achternaam) LIKE :naam )";
		} elseif ($zoekveld == 'adres') {
			$zoekfilter = "adres LIKE :containsZoekterm OR woonplaats LIKE :containsZoekterm OR
				postcode LIKE :containsZoekterm OR REPLACE(postcode, ' ', '') LIKE :containsZonderSpatiesZoekterm";
			$zoekfilterparams[':containsZoekterm']=  $zoekterm;
			$zoekfilterparams[':containsZonderSpatiesZoekterm']=  $containsZonderSpatiesZoekterm;
		} else {
			if (preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld == 'uid' OR $zoekveld == 'naam')) {
				//zoeken op lichtingen...
				$zoekfilter = "SUBSTRING(uid, 1, 2)=:zoekterm";
				$zoekfilterparams[':zoekterm']=  $zoekterm;

			} else {
				$zoekfilter = "{$zoekveld} LIKE :containsZoekterm";
				$zoekfilterparams[':containsZoekterm']=  $zoekterm;
			}
		}

		# In welke status wordt gezocht, is afhankelijk van wat voor rechten de
		# ingelogd persoon heeft.
		#
		# R_LID en R_OUDLID hebben beide P_LEDEN_READ en P_OUDLEDEN_READ en kunnen
		# de volgende afkortingen gebruiken:
		#  - '' (lege string) of alleleden: novieten, (gast)leden, kringels, ere- en oudleden
		#  - leden :  						novieten, (gast)leden en kringels
		#  - oudleden : 					oud- en ereleden
		#  - allepersonen:					novieten, (gast)leden, kringels, oud- en ereleden, overleden leden en nobodies (alleen geen commissies)
		# én alleen voor OUDLEDENMOD:
		#  - nobodies : 					alleen nobodies

		$statusfilter = '';
		if ($zoekstatus == 'alleleden') {
			$zoekstatus = '';
		}
		if ($zoekstatus == 'allepersonen') {
			$zoekstatus = array('S_NOVIET', 'S_LID', 'S_GASTLID', 'S_OUDLID', 'S_ERELID', 'S_KRINGEL', 'S_OVERLEDEN', 'S_NOBODY', 'S_EXLID');
		}
		if (is_array($zoekstatus)) {
			//we gaan nu gewoon simpelweg statussen aan elkaar plakken. LET OP: deze functie doet nu
			//geen controle of een gebruiker dat mag, dat moet dus eerder gebeuren.
			$statusfilter = "status='" . implode("' OR status='", $zoekstatus) . "'";
		} else {
			# we zoeken in leden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
			if (
				(LoginModel::mag(P_LEDEN_READ) and !LoginModel::mag(P_OUDLEDEN_READ)) or (LoginModel::mag(P_LEDEN_READ) and LoginModel::mag(P_OUDLEDEN_READ) and $zoekstatus != 'oudleden')
			) {
				$statusfilter .= "status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'";
			}
			# we zoeken in oudleden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
			if (
				(!LoginModel::mag(P_LEDEN_READ) and LoginModel::mag(P_OUDLEDEN_READ)) or (LoginModel::mag(P_LEDEN_READ) and LoginModel::mag(P_OUDLEDEN_READ) and $zoekstatus != 'leden')
			) {
				if ($statusfilter != '')
					$statusfilter .= " OR ";
				$statusfilter .= "status='S_OUDLID' OR status='S_ERELID'";
			}
			# we zoeken in nobodies als
			# de ingelogde persoon dat mag EN daarom gevraagd heeft
			if ($zoekstatus === 'nobodies' and LoginModel::mag(P_LEDEN_MOD)) {
				# alle voorgaande filters worden ongedaan gemaakt en er wordt alleen op nobodies gezocht
				$statusfilter = "status='S_NOBODY' OR status='S_EXLID'";
			}

			if (LoginModel::mag(P_LEDEN_READ) and $zoekstatus === 'novieten') {
				$statusfilter = "status='S_NOVIET'";
			}
		}

		# als er een specifieke moot is opgegeven, gaan we alleen in die moot zoeken
		if (($verticale != 'alle')) {
			$mootfilter = 'AND verticale = :verticale ';
			$zoekfilterparams[':verticale'] = $verticale;
		} else {
			$mootfilter = '';
		}

		# is er een maximum aantal resultaten gewenst
		if ((int)$limiet > 0) {
			$limit = (int)$limiet;
		} else {
			$limit = null;
		}

		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {
			$result = ProfielModel::instance()->find("($zoekfilter) AND ($statusfilter) $mootfilter", $zoekfilterparams, null, $sort, $limit);

			return $result->fetchAll();
		}

		return [];
	}
}
