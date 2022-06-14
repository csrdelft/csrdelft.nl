<?php

namespace CsrDelft\service;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\profiel\ProfielToestemmingProxy;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\lid\LLCSV;
use CsrDelft\view\lid\LLKaartje;
use CsrDelft\view\lid\LLLijst;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * LidZoeker
 *
 * de array's die in deze class staan bepalen wat er in het formulier te zien is.
 */
class LidZoekerService
{
	//velden die door gewone leden geselecteerd mogen worden.
	public $veldNamen = [
		'telefoon' => 'Nummer',
		'mobiel' => 'Pauper',
		'studie' => 'Studie',
		'gebdatum' => 'Geb.datum',
		'ontvangtcontactueel' => 'Contactueel?',
		'machtiging' => 'Machtiging getekend?',
		'adresseringechtpaar' => 'Post echtpaar t.n.v.',
		'linkedin' => 'LinkedIn',
	];
	//velden die ook door mensen met P_LEDEN_MOD bekeken mogen worden
	//(merge in de constructor)
	private $allowVelden = [
		'pasfoto',
		'uid',
		'naam',
		'voorletters',
		'voornaam',
		'tussenvoegsel',
		'achternaam',
		'nickname',
		'geslacht',
		'email',
		'adres',
		'telefoon',
		'mobiel',
		'studie',
		'status',
		'gebdatum',
		'beroep',
		'verticale',
		'lidjaar',
		'kring',
		'patroon',
		'woonoord',
	];
	//deze velden kunnen we niet selecteren voor de ledenlijst, ze zijn wel te
	//filteren en te sorteren.
	private $allowVeldenLEDENMOD = [
		'eetwens',
		'moot',
		'muziek',
		'ontvangtcontactueel',
		'kerk',
		'lidafdatum',
		'echtgenoot',
		'adresseringechtpaar',
		'land',
		'bankrekening',
		'machtiging',
	];
	//velden die wel selecteerbaar zijn, maar niet in de db bestaan
	private $veldenNotSelectable = ['voornaam', 'achternaam', 'tussenvoegsel'];
	//nette aliassen voor kolommen, als ze niet beschikbaar zijn wordt gewoon
	//de naam uit $this->allowVelden gebruikt
	private $veldenNotindb = ['pasfoto'];
	//toegestane opties voor het statusfilter.
	private $allowStatus;
	//toegestane opties voor de weergave.
	private $allowWeergave = [
		'lijst' => LLLijst::class,
		'kaartje' => LLKaartje::class,
		'csv' => LLCSV::class,
	];
	private $sortable = [
		'achternaam' => 'Achternaam',
		'email' => 'Email',
		'gebdatum' => 'Geboortedatum',
		'lidjaar' => 'lichting',
		'studie' => 'Studie',
	];
	//standaardwaarden voor het zoeken zonder parameters
	private $rawQuery = ['status' => 'LEDEN', 'sort' => 'achternaam'];
	private $query = '';
	private $filters = [];
	private $sort = ['achternaam'];
	private $velden = ['naam', 'email', 'telefoon', 'mobiel'];
	private $weergave = LLLijst::class;
	private $result = null;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;
	/**
	 * @var VerticalenRepository
	 */
	private $verticalenRepository;

	public function __construct(
		EntityManagerInterface $em,
		ProfielRepository $profielRepository,
		Security $security,
		VerticalenRepository $verticalenRepository,
		LidToestemmingRepository $lidToestemmingRepository
	) {
		$this->allowStatus = LidStatus::getEnumValues();

		//wat extra velden voor moderators.
		if ($security->isGranted('ROLE_LEDEN_MOD')) {
			$this->allowVelden = array_merge(
				$this->allowVelden,
				$this->allowVeldenLEDENMOD
			);
		}

		//parse default values.
		$this->parseQuery($this->rawQuery);
		$this->profielRepository = $profielRepository;
		$this->security = $security;
		$this->em = $em;
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->verticalenRepository = $verticalenRepository;
	}

	public function parseQuery($query)
	{
		$this->result = null; //nieuwe parameters, oude resultaat wegmikken.

		if (!is_array($query)) {
			$query = explode('&', $query);
		}
		$this->rawQuery = $query;

		//als er geen explicite status is opgegeven, en het zoekende lid is oudlid, dan zoeken we automagisch ook in de oudleden.
		if (
			!isset($query['status']) &&
			$this->security->getUser()->profiel->isOudlid()
		) {
			$this->rawQuery['status'] = 'LEDEN|OUDLEDEN';
		}

		foreach ($this->rawQuery as $key => $value) {
			switch ($key) {
				case 'q':
					$this->query = $value;
					break;

				case 'weergave':
					if (isset($this->allowWeergave[$value])) {
						$this->weergave = $this->allowWeergave[$value];
					}
					break;

				case 'velden':
					$this->velden = [];
					foreach ($value as $veld) {
						if (array_key_exists($veld, $this->getSelectableVelden())) {
							$this->velden[] = $veld;
						}
					}
					if (count($this->velden) == 0) {
						$this->velden = ['naam', 'adres', 'email', 'mobiel'];
					}
					break;

				case 'status':
					$value = strtoupper($value);
					//als op alle lid-statussen moet worden gezocht verwijderen we
					//eventueel aanwezige filters en zoeken we in alles.
					if ($value == '*' || $value == 'ALL') {
						if (isset($this->filters['status'])) {
							unset($this->filters['status']);
						}
						break;
					}
					$filters = explode('|', $value);

					$add = [];
					foreach ($filters as $filter) {
						if ($filter == 'LEDEN') {
							$add = array_merge($add, LidStatus::getLidLike());
							continue;
						}
						if ($filter == 'OUDLEDEN') {
							$add = array_merge($add, LidStatus::getOudlidLike());
							continue;
						}
						$filter = 'S_' . $filter;
						if (in_array($filter, $this->allowStatus)) {
							$add[] = $filter;
						}
					}
					$this->addFilter('status', $add);
					break;

				case 'sort':
					if (array_key_exists($value, $this->getSortableVelden())) {
						$this->sort = [$value];
					}
					break;
			}
		}
	}

	//lijst met velden die bruikbaar zijn in een '<veld>:=?<zoekterm>'-zoekopdracht.

	public function getSelectableVelden()
	{
		$return = [];
		foreach ($this->allowVelden as $veld) {
			if (in_array($veld, $this->veldenNotSelectable)) {
				continue;
			}
			if (isset($this->veldNamen[$veld])) {
				$return[$veld] = $this->veldNamen[$veld];
			} else {
				$return[$veld] = $veld;
			}
		}
		return $return;
	}

	public function addFilter($field, $value)
	{
		if (is_array($value)) {
			$this->filters[$field] = $value;
		} else {
			$this->filters[$field] = [$value];
		}
	}

	public function getSortableVelden()
	{
		return $this->sortable;
	}

	public function count()
	{
		if ($this->result === null) {
			$this->search();
		}
		return count($this->result);
	}

	/**
	 * Doe de zoektocht.
	 */
	public function search()
	{
		$this->result = [];
		$qb = $this->profielRepository->createQueryBuilder('p');

		if (trim($this->query) == '') {
			return;
		}

		$this->defaultSearch($qb, $this->query);
		$this->getFilterSQL($qb);

		/** @var Profiel[] $result */
		$result = $qb->getQuery()->getResult();

		foreach ($result as $profiel) {
			if ($this->magProfielVinden($profiel, $this->query)) {
				$this->result[] = new ProfielToestemmingProxy(
					$profiel,
					$this->lidToestemmingRepository
				);
			}
		}
	}

	/**
	 * Stel een setje WHERE-voorwaarden samen waarin standaard wordt gezocht.
	 * @param QueryBuilder $queryBuilder
	 * @param $zoekterm
	 * @return QueryBuilder
	 */
	private function defaultSearch(QueryBuilder $queryBuilder, $zoekterm)
	{
		if (preg_match('/^groep:([0-9]+|[a-z]+)$/i', $zoekterm)) {
			//leden van een groep
			$uids = [];
			/*try {
				//FIXME: $groep = new OldGroep(substr($zoekterm, 6));
				$uids = array_keys($groep->getLeden());
			} catch (\Exception $e) {
				//care.
			}*/
			$queryBuilder->where('p.uid in (:uids)');
			$queryBuilder->setParameter('uids', $uids);
		} elseif (preg_match('/^verticale:\w*$/', $zoekterm)) {
			//verticale, id, letter
			$v = substr($zoekterm, 10);
			if (strlen($v) > 1) {
				$result = $this->verticalenRepository->searchByNaam($v);
				$verticales = [];
				if ($result) {
					$verticales[] = $result->letter;
				}
				$queryBuilder->where('p.verticale in (:verticales)');
				$queryBuilder->setParameter('verticales', $verticales);
			} else {
				$verticale = $this->verticalenRepository->get($v);
				if ($verticale) {
					$queryBuilder->where('p.verticale = :verticale');
					$queryBuilder->setParameter('verticale', $verticale->letter);
				} else {
					$queryBuilder->where('p.verticale = \'\'');
				}
			}
		} elseif (preg_match('/^\d{2}$/', $zoekterm)) {
			//lichting bij een string van 2 cijfers
			$queryBuilder->where('p.lidjaar LIKE :zoekterm');
			$queryBuilder->setParameter('zoekterm', '__' . $zoekterm);
		} elseif (preg_match('/^lichting:\d\d(\d\d)?$/', $zoekterm)) {
			//lichting op de explicite manier
			$lichting = substr($zoekterm, 9);
			if (strlen($lichting) == 4) {
				$queryBuilder->where('p.lidjaar = :lidjaar');
				$queryBuilder->setParameter('lidjaar', $lichting);
			} else {
				$queryBuilder->where('p.lidjaar LIKE :lidjaar');
				$queryBuilder->setParameter('lidjaar', '__' . $lichting);
			}
		} elseif (preg_match('/^[a-z0-9][0-9]{3}$/', $zoekterm)) {
			//uid's is ook niet zo moeilijk.
			$queryBuilder->where('p.uid = :uid');
			$queryBuilder->setParameter('uid', $zoekterm);
		} elseif (
			preg_match('/^([a-z0-9][0-9]{3} ?,? ?)*([a-z0-9][0-9]{3})$/', $zoekterm)
		) {
			//meerdere uid's gescheiden door komma's.
			//explode en trim() elke waarde van de array.
			$uids = array_map('trim', explode(',', $zoekterm));
			$queryBuilder->where('p.uid in (:uids)');
			$queryBuilder->setParameter('uids', $uids);
		} elseif (
			preg_match(
				'/^(' .
					implode('|', $this->getDBVeldenAllowed()) .
					'):=?([a-z0-9\-_])+$/i',
				$zoekterm
			)
		) {
			//Zoeken in de velden van $this->allowVelden. Zoektermen met 'veld:' ervoor.
			//met 'veld:=<zoekterm> wordt exact gezocht.
			$parts = explode(':', $zoekterm);

			$veld = strtolower($parts[0]);

			if ($parts[1][0] == '=') {
				$queryBuilder->where(
					$queryBuilder->expr()->eq('p.' . $veld, ':zoekterm')
				);
				$queryBuilder->setParameter('zoekterm', substr($parts[1], 1));
			} else {
				$queryBuilder->where(
					$queryBuilder->expr()->like('p.' . $veld, ':zoekterm')
				);
				$queryBuilder->setParameter('zoekterm', sql_contains($parts[1]));
			}
		} else {
			//als niets van hierboven toepasselijk is zoeken we in zo ongeveer alles

			$zoekExpr = $queryBuilder
				->expr()
				->orX()
				->add('p.voornaam LIKE :zoekterm')
				->add('p.achternaam LIKE :zoekterm')
				->add(
					'CONCAT_WS(\' \', p.voornaam, p.tussenvoegsel, p.achternaam) LIKE :zoekterm'
				)
				->add('CONCAT_WS(\' \', p.voornaam, p.achternaam) LIKE :zoekterm')
				->add('CONCAT_WS(\' \', p.tussenvoegsel, p.achternaam) LIKE :zoekterm')
				->add('CONCAT_WS(\' \', p.achternaam, p.tussenvoegsel) LIKE :zoekterm')
				->add('p.nickname LIKE :zoekterm')
				->add(
					'CONCAT_WS(\' \', p.adres, p.postcode, p.woonplaats) LIKE :zoekterm'
				)
				->add('p.adres LIKE :zoekterm')
				->add('p.postcode LIKE :zoekterm')
				->add('p.woonplaats LIKE :zoekterm')
				->add('p.mobiel LIKE :zoekterm')
				->add('p.telefoon LIKE :zoekterm')
				->add('p.studie LIKE :zoekterm')
				->add('p.email LIKE :zoekterm');

			if ($this->security->isGranted('ROLE_LEDEN_MOD')) {
				$zoekExpr->add('p.eetwens LIKE :zoekterm');
			}

			$queryBuilder->where($zoekExpr);
			$queryBuilder->setParameter('zoekterm', sql_contains($zoekterm));
		}

		return $queryBuilder;
	}

	private function getDBVeldenAllowed()
	{
		//hier staat eigenlijk $a - $b, maar die heeft php niet.
		return array_intersect(
			array_diff($this->allowVelden, $this->veldenNotindb),
			$this->allowVelden
		);
	}

	public function getFilterSQL(QueryBuilder $queryBuilder)
	{
		$andExpr = $queryBuilder->expr()->andX();

		foreach ($this->filters as $key => $value) {
			if (is_array($value)) {
				$andExpr->add("p.{$key} IN (:{$key})");
			} else {
				$andExpr->add("p.{$key} = :{$key}");
			}
			$queryBuilder->setParameter($key, $value);
		}
		$queryBuilder->andWhere($andExpr);

		return $queryBuilder;
	}

	/**
	 * Geef terug of een bepaald resultaat in de zoekresultaten mag zitten.
	 *
	 * @param $profiel
	 * @param string $query
	 * @return bool
	 */
	private function magProfielVinden(Profiel $profiel, string $query)
	{
		// Als de zoekquery in de naam zit, geef dan altijd dit profiel terug als resultaat.
		$zoekvelden = $this->lidToestemmingRepository->getModuleKeys('profiel');

		if (strpos($profiel->getNaam(), $query) !== false) {
			return true;
		}

		foreach ($zoekvelden as $veld) {
			if ($veld === 'status') {
				continue;
			}

			$zichtbaar = $this->lidToestemmingRepository->toestemming(
				$profiel,
				$veld
			);

			$queryInVeld =
				is_string($profiel->$veld) &&
				$query !== '' &&
				strpos($profiel->$veld, $query) !== false;

			// Geef dit profiel niet terug als een niet zichtbaar veld de query bevat.
			if (!$zichtbaar && $queryInVeld) {
				return false;
			}
		}

		return true;
	}

	public function searched()
	{
		return $this->result !== null;
	}

	/*
	 * Zet een array met $key => value om in SQL. Als $value een array is,
	 * komt er een $key IN ( value0, value1, etc. ) uit.
	 */

	public function getLeden()
	{
		if ($this->result === null) {
			$this->search();
		}
		return $this->result;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function getVelden()
	{
		return $this->velden;
	}

	public function getWeergave()
	{
		return $this->weergave;
	}

	public function getRawQuery($key)
	{
		if (!isset($this->rawQuery[$key])) {
			return false;
		}
		return $this->rawQuery[$key];
	}

	public function getSelectedVelden()
	{
		return $this->velden;
	}

	public function __toString()
	{
		$return = 'Zoeker:';
		$return .= print_r($this->rawQuery, true);
		$return .= print_r($this->filters, true);
		return $return;
	}
}
