<?php

namespace CsrDelft\service;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class VerjaardagenService
{
	const FILTER_BY_TOESTEMMING = "INNER JOIN lidtoestemmingen t ON T2.uid  = t.uid AND t.waarde = 'ja' AND t.module = 'profiel' AND t.instelling = 'gebdatum'";
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		Security $security,
		ProfielRepository $profielRepository,
		EntityManagerInterface $em
	) {
		$this->security = $security;
		$this->profielRepository = $profielRepository;
		$this->em = $em;
	}

	private function getFilterByToestemmingSql()
	{
		return $this->security->isGranted(P_LEDEN_MOD) ? '' : self::FILTER_BY_TOESTEMMING;
	}

	private function getNovietenFilter()
	{
		if ($this->em->getFilters()->isEnabled('verbergNovieten')) {
			$jaar = intval(
				trim(
					$this->em
						->getFilters()
						->getFilter('verbergNovieten')
						->getParameter('jaar'),
					"'"
				)
			);
			return "AND NOT (STATUS = 'S_NOVIET' AND lidjaar = $jaar)";
		}

		return '';
	}

	/**
	 * @return Profiel[][]
	 */
	public function getJaar()
	{
		return array_map([$this, 'get'], range(1, 12));
	}

	/**
	 * @param $maand
	 *
	 * @return Profiel[]
	 */
	public function get($maand)
	{
		$qb = $this->profielRepository
			->createQueryBuilder('p')
			->where('p.status in (:lidstatus) and MONTH(p.gebdatum) = :maand')
			->setParameter(
				'lidstatus',
				array_merge(LidStatus::getLidLike(), [LidStatus::Kringel])
			)
			->setParameter('maand', $maand)
			->orderBy('DAY(p.gebdatum)');

		if (!$this->security->isGranted(P_LEDEN_MOD)) {
			static::filterByToestemming($qb, 'profiel', 'gebdatum');
		}

		return $qb->getQuery()->getResult();
	}

	public static function filterByToestemming(
		QueryBuilder $queryBuilder,
		$module,
		$instelling,
		$profielAlias = 'p'
	) {
		return $queryBuilder
			->andWhere(
				't.waarde = \'ja\' and t.module = :t_module and t.instelling = :t_instelling'
			)
			->setParameter('t_module', $module)
			->setParameter('t_instelling', $instelling)
			->join($profielAlias . '.toestemmingen', 't');
	}

	/**
	 * @param int $aantal
	 *
	 * @return Profiel[]
	 */
	public function getKomende($aantal = 10)
	{
		$rsm = new ResultSetMappingBuilder($this->em);
		$rsm->addRootEntityFromClassMetadata(Profiel::class, 'p');
		$rsm->addJoinedEntityFromClassMetadata(Account::class, 'a', 'p', 'account', ['uid' => 'account_uid', 'email' => 'account_email']);

		$select = $rsm->generateSelectClause(['p' => 'T2']);

		$lidstatus =
			"'" .
			implode(
				"', '",
				array_merge(LidStatus::getLidLike(), [LidStatus::Kringel])
			) .
			"'";

		$query = <<<SQL
SELECT $select, DATEDIFF(volgende_verjaardag, NOW()) AS distance
FROM (
    SELECT *, ADDDATE(verjaardag, INTERVAL verjaardag < DATE(NOW()) YEAR) AS volgende_verjaardag
    FROM (
        SELECT profielen.*, ADDDATE(gebdatum, INTERVAL YEAR(NOW()) - YEAR(gebdatum) YEAR) AS verjaardag
        FROM profielen
        WHERE status IN ($lidstatus)
        {$this->getNovietenFilter()}
        ) AS T1
    ) AS T2
LEFT JOIN accounts a using (uid)
{$this->getFilterByToestemmingSql()}
ORDER BY distance
LIMIT :limit
SQL;

		return $this->em
			->createNativeQuery($query, $rsm)
			->setParameter('limit', $aantal)
			->getResult();
	}

	/**
	 * Als je deze methode aanpast, controleer dan of deze goed werkt met schrikkeljaren en als van en tot in
	 * verschillende jaren liggen. Er wordt wel aangenomen dat de afstand tussen van en tot maximaal een jaar is.
	 *
	 * @param DateTimeInterface $van
	 * @param DateTimeInterface $tot
	 * @param int $limiet
	 *
	 * @return Profiel[]
	 */
	public function getTussen(
		DateTimeInterface $van,
		DateTimeInterface $tot,
		$limiet = null
	) {
		$rsm = new ResultSetMappingBuilder($this->em);
		// We selecteren eerst een profiel.
		$rsm->addRootEntityFromClassMetadata(Profiel::class, 'p');
		// Voeg een joined entity toe, want de OneToOne relatie tussen Profiel en account _moet_ geladen worden omdat Profiel de owner is.
		// Hernoem kolommen die in beide entities voorkomen.
		$rsm->addJoinedEntityFromClassMetadata(Account::class, 'a', 'p', 'account', ['uid' => 'account_uid', 'email' => 'account_email']);

		// Genereer een select, alle profiel ('p') velden zijn te vinden in 'T2' in de query, en account ('a') in 'a'.
		$select = $rsm->generateSelectClause(['p' => 'T2']);

		$lidstatus =
			"'" .
			implode(
				"', '",
				array_merge(LidStatus::getLidLike(), [LidStatus::Kringel])
			) .
			"'";

		$query = <<<SQL
SELECT $select
FROM (
    SELECT *, ADDDATE(verjaardag, INTERVAL verjaardag < DATE(:van_datum) YEAR) AS volgende_verjaardag
    FROM (
        SELECT profielen.*, ADDDATE(gebdatum, INTERVAL YEAR(DATE(:van_datum)) - YEAR(gebdatum) YEAR) AS verjaardag
        FROM profielen
        WHERE status IN ($lidstatus)
        {$this->getNovietenFilter()}
        ) AS T1
    ) AS T2
LEFT JOIN accounts a using (uid)
{$this->getFilterByToestemmingSql()}
WHERE volgende_verjaardag >= DATE(:van_datum) AND volgende_verjaardag <= DATE(:tot_datum)
ORDER BY volgende_verjaardag
SQL;

		if ($limiet != null) {
			$query .= 'LIMIT ' . (int) $limiet;
		}

		return $this->em
			->createNativeQuery($query, $rsm)
			->setParameter('van_datum', $van)
			->setParameter('tot_datum', $tot)
			->getResult();
	}
}
