<?php

namespace CsrDelft\service;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2018
 */
class ProfielService {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(ProfielRepository $profielRepository) {
		$this->profielRepository = $profielRepository;
	}

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
		$queryBuilder = $this->profielRepository->createQueryBuilder('p');
		$expr = $queryBuilder->expr();
		$containsZonderSpatiesZoekterm = sql_contains(str_replace(' ', '', $zoekterm));
		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if ($zoekveld == 'naam' && !preg_match('/^\d{2}$/', $zoekterm)) {
			if (preg_match('/ /', trim($zoekterm))) {
				$zoekdelen = explode(' ', $zoekterm);
				$iZoekdelen = count($zoekdelen);
				if ($iZoekdelen == 2) {
					$queryBuilder
						->where($expr->orX()
							->add('p.voornaam LIKE :voornaam AND p.achternaam LIKE :achternaam')
							->add('p.voornaam LIKE :containsZoekterm')
							->add('p.achternaam LIKE :containsZoekterm')
							->add('p.nickname LIKE :containsZoekterm')
							->add('p.uid LIKE :containsZoekterm')
						)
						->setParameter('voornaam', sql_contains($zoekdelen[0]))
						->setParameter('achternaam', sql_contains($zoekdelen[1]))
						->setParameter('containsZoekterm', sql_contains($zoekterm));
				} else {
					$queryBuilder
						->where('p.voornaam LIKE :voornaam and p.achternaam LIKE :achternaam')
						->setParameter('voornaam', sql_contains($zoekdelen[0]))
						->setParameter('achternaam', sql_contains($zoekdelen[$iZoekdelen - 1]));
				}
			} else {
				$queryBuilder
					->where($expr->orX()
						->add('p.voornaam LIKE :containsZoekterm')
						->add('p.achternaam LIKE :containsZoekterm')
						->add('p.nickname LIKE :containsZoekterm')
						->add('p.uid LIKE :containsZoekterm')
					)
					->setParameter('containsZoekterm', sql_contains($zoekterm));
			}

			$queryBuilder
				->orWhere('CONCAT_WS(\' \', p.voornaam, p.tussenvoegsel, p.achternaam) LIKE :naam')
				->orWhere('CONCAT_WS(\' \', p.voornaam, p.achternaam) LIKE :naam')
				->setParameter('naam', sql_contains($zoekterm));
		} elseif ($zoekveld == 'adres') {
			$queryBuilder
				->where($expr->orX()
					->add('p.adres LIKE :containsZoekterm')
					->add('p.woonplaats LIKE :containsZoekterm')
					->add('p.postcode LIKE :containsZoekterm')
					->add('REPLACE(p.postcode, \' \', \'\') LIKE :containsZonderSpatiesZoekterm')
				)
				->setParameter('containsZoekterm', sql_contains($zoekterm))
				->setParameter('containsZonderSpatiesZoekterm', $containsZonderSpatiesZoekterm);
		} else {
			if (preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld == 'uid' OR $zoekveld == 'naam')) {
				//zoeken op lichtingen...
				$queryBuilder
					->where('p.uid LIKE :uid')
					->setParameter('uid', $zoekterm . '__');
			} else {
				$queryBuilder
					->where("p.{$zoekveld} LIKE :containsZoekterm")
					->setParameter('containsZoekterm', sql_contains($zoekterm));
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

		if ($zoekstatus == 'alleleden') {
			$zoekstatus = '';
		}
		if ($zoekstatus == 'allepersonen') {
			$zoekstatus = LidStatus::getEnumValues();
		}

		$statussen = [];
		if (is_array($zoekstatus)) {
			//we gaan nu gewoon simpelweg statussen aan elkaar plakken. LET OP: deze functie doet nu
			//geen controle of een gebruiker dat mag, dat moet dus eerder gebeuren.
			$statussen = $zoekstatus;
		} else {
			# we zoeken in leden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
			if (
				(LoginService::mag(P_LEDEN_READ) && !LoginService::mag(P_OUDLEDEN_READ)) || (LoginService::mag(P_LEDEN_READ) && LoginService::mag(P_OUDLEDEN_READ) && $zoekstatus != 'oudleden')
			) {
				$statussen[] = LidStatus::Lid;
				$statussen[] = LidStatus::Gastlid;
				$statussen[] = LidStatus::Noviet;
				$statussen[] = LidStatus::Kringel;
			}
			# we zoeken in oudleden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
			if (
				(!LoginService::mag(P_LEDEN_READ) && LoginService::mag(P_OUDLEDEN_READ)) || (LoginService::mag(P_LEDEN_READ) && LoginService::mag(P_OUDLEDEN_READ) && $zoekstatus != 'leden')
			) {
				$statussen[] = LidStatus::Oudlid;
				$statussen[] = LidStatus::Erelid;
			}
			# we zoeken in nobodies als
			# de ingelogde persoon dat mag EN daarom gevraagd heeft
			if ($zoekstatus === 'nobodies' && LoginService::mag(P_LEDEN_MOD)) {
				# alle voorgaande filters worden ongedaan gemaakt en er wordt alleen op nobodies gezocht
				$statussen = [LidStatus::Nobody, LidStatus::Exlid];
			}

			if (LoginService::mag(P_LEDEN_READ) && $zoekstatus === 'novieten') {
				$statussen = [LidStatus::Noviet];
			}
		}

		$queryBuilder
			->andWhere('p.status in (:zoekstatus)')
			->setParameter('zoekstatus', $statussen);

		# als er een specifieke moot is opgegeven, gaan we alleen in die moot zoeken
		if ($verticale != 'alle') {
			$queryBuilder
				->andWhere('p.verticale = :verticale')
				->setParameter('verticale', $verticale);
		}

		# is er een maximum aantal resultaten gewenst
		$queryBuilder
			->orderBy('p.' . $sort)
			->setMaxResults((int)$limiet > 0 ? (int)$limiet : null);

		return $queryBuilder->getQuery()->getResult();
	}
}
