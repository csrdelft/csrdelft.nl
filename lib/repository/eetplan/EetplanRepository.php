<?php

namespace CsrDelft\repository\eetplan;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Verzorgt het opvragen van eetplangegevens
 *
 * @method Eetplan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Eetplan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Eetplan[]    findAll()
 * @method Eetplan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Eetplan|null retrieveByUuid($UUID)
 */
class EetplanRepository extends AbstractRepository
{
	const FMT_DATE = 'dd-MM-Y';

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Eetplan::class);
	}

	public function avondHasEetplan($avond): bool
	{
		return count($this->findBy(['avond' => $avond])) > 0;
	}

	/**
	 * Haal alle avonden op die voor deze lichting gelden.
	 *
	 * @param $lidjaar
	 *
	 * @return Eetplan[] Lijst met eetplan objecten met alleen een avond.
	 */
	public function getAvonden($lidjaar): mixed
	{
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.lidjaar = :lidjaar and e.avond is not null')
			->setParameter('lidjaar', $lidjaar)
			->groupBy('e.avond')
			->getQuery()
			->getResult();
	}

	/**
	 * Haal het volledige eetplan op (voor de huidige lichting)
	 *
	 * Uitvoer is een array met 'uid' => [Eetplan, Eetplan, ...]
	 *
	 * @param $lidjaar
	 *
	 * @return array Het eetplan
	 */
	public function getEetplan($lidjaar): array
	{
		// Avond null wordt gebruikt voor novieten die huizen kennen
		// Orderen bij avond, zodat de avondvolgorde per noviet klopt
		/** @var Eetplan[] $eetplan */
		$eetplan = $this->createQueryBuilder('e')
			->join('e.noviet', ' n')
			->where('n.lidjaar = :lidjaar and e.avond is not null')
			->setParameter('lidjaar', $lidjaar)
			->orderBy('e.avond', 'DESC')
			->getQuery()
			->getResult();
		$eetplanFeut = [];
		$avonden = [];
		foreach ($eetplan as $sessie) {
			if (!isset($eetplanFeut[$sessie->noviet->uid])) {
				$eetplanFeut[$sessie->noviet->uid] = [
					'avonden' => [],
					'uid' => $sessie->noviet->uid,
					'naam' => $sessie->noviet->getNaam(),
				];
			}

			$eetplanFeut[$sessie->noviet->uid]['avonden'][] = [
				'datum' => $sessie->avond,
				'woonoord_id' => $sessie->woonoord->id,
				'woonoord' => $sessie->woonoord->naam,
			];

			if (
				!isset(
					$avonden[DateUtil::dateFormatIntl($sessie->avond, self::FMT_DATE)]
				)
			) {
				$avonden[DateUtil::dateFormatIntl($sessie->avond, self::FMT_DATE)] =
					$sessie->avond;
			}
		}

		return [
			'novieten' => array_values($eetplanFeut),
			'avonden' => array_values($avonden),
		];
	}

	/**
	 * @param string $uid Uid van de feut
	 *
	 * @return Eetplan[]|false lijst van eetplansessies voor deze feut, gesorteerd op datum (oplopend)
	 */
	public function getEetplanVoorNoviet($uid): mixed
	{
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.uid = :uid and e.avond is not null')
			->setParameter('uid', $uid)
			->orderBy('e.avond', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @param int $woonoord_id Id van het huis
	 * @param string $lidjaar
	 *
	 * @return Eetplan[] lijst van eetplansessies voor dit huis, gegroepeerd op avond (oplopend)
	 */
	public function getEetplanVoorHuis($woonoord_id, $lidjaar): mixed
	{
		/** @var Eetplan[] $sessies */
		$sessies = $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->join('e.woonoord', 'w')
			->where(
				'n.lidjaar = :lidjaar and w.id = :woonoord_id and e.avond is not null'
			)
			->setParameter('lidjaar', $lidjaar)
			->setParameter('woonoord_id', $woonoord_id)
			->orderBy('e.avond', 'ASC')
			->getQuery()
			->getResult();

		return array_reduce(
			$sessies,
			function (array $accumulator, Eetplan $eetplan) {
				$accumulator[
					DateUtil::dateFormatIntl($eetplan->avond, self::FMT_DATE)
				][] = $eetplan;

				return $accumulator;
			},
			[]
		);
	}

	/**
	 * @param string $lidjaar
	 *
	 * @return Eetplan[]
	 */
	public function getBekendeHuizen($lidjaar): mixed
	{
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.lidjaar = :lidjaar and e.avond is null')
			->setParameter('lidjaar', $lidjaar)
			->getQuery()
			->getResult();
	}

	/**
	 * @param string $avond
	 * @param string $lichting
	 */
	public function verwijderEetplan($avond, $lichting): void
	{
		$alleEetplan = $this->getEetplanVoorAvond($avond, $lichting);

		foreach ($alleEetplan as $eetplan) {
			$this->remove($eetplan);
		}
	}

	/**
	 * @param string $avond
	 *
	 * @param $lidjaar
	 * @return Eetplan[]
	 */
	public function getEetplanVoorAvond($avond, $lidjaar): mixed
	{
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('e.avond = :avond and n.lidjaar = :lidjaar')
			->setParameter('avond', $avond)
			->setParameter('lidjaar', $lidjaar)
			->getQuery()
			->getResult();
	}

	/**
	 * @param int $lidjaar
	 * @return int|mixed|string
	 */
	public function getBezocht(int $lidjaar): mixed
	{
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.lidjaar like :lidjaar')
			->setParameter('lidjaar', $lidjaar)
			->getQuery()
			->getResult();
	}
}
