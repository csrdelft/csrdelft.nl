<?php

namespace CsrDelft\repository\eetplan;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\EetplanFactory;
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
class EetplanRepository extends AbstractRepository {
	const FMT_DATE = "dd-MM-Y";

	/**
	 * @var EetplanBekendenRepository
	 */
	private $eetplanBekendenRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(EetplanBekendenRepository $eetplanBekendenRepository, ProfielRepository $profielRepository, ManagerRegistry $registry) {
		parent::__construct($registry, Eetplan::class);

		$this->eetplanBekendenRepository = $eetplanBekendenRepository;
		$this->profielRepository = $profielRepository;
	}

	public function avondHasEetplan($avond) {
		return count($this->findBy(['avond' => $avond])) > 0;
	}

	/**
	 * Haal alle avonden op die voor deze lichting gelden.
	 *
	 * @param $lichting
	 *
	 * @return Eetplan[] Lijst met eetplan objecten met alleen een avond.
	 */
	public function getAvonden($lichting) {
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.uid like :uid and e.avond is not null')
			->setParameter('uid', $lichting . '%')
			->groupBy('e.avond')
			->getQuery()->getResult();
	}

	/**
	 * Haal het volledige eetplan op (voor de huidige lichting)
	 *
	 * Uitvoer is een array met 'uid' => [Eetplan, Eetplan, ...]
	 *
	 * @param $lichting
	 *
	 * @return array Het eetplan
	 */
	public function getEetplan($lichting) {
		// Avond null wordt gebruikt voor novieten die huizen kennen
		// Orderen bij avond, zodat de avondvolgorde per noviet klopt
		/** @var Eetplan[] $eetplan */
		$eetplan = $this->createQueryBuilder('e')
			->join('e.noviet', ' n')
			->where('n.uid like :uid and e.avond is not null')
			->setParameter('uid', $lichting . '%')
			->orderBy('e.avond', 'DESC')
			->getQuery()->getResult();
		$eetplanFeut = [];
		$avonden = [];
		foreach ($eetplan as $sessie) {
			if (!isset($eetplanFeut[$sessie->noviet->uid])) {
				$eetplanFeut[$sessie->noviet->uid] = [
					'avonden' => [],
					'uid' => $sessie->noviet->uid,
					'naam' => $sessie->noviet->getNaam()
				];
			}

			$eetplanFeut[$sessie->noviet->uid]['avonden'][] = [
				'datum' => $sessie->avond,
				'woonoord_id' => $sessie->woonoord->id,
				'woonoord' => $sessie->woonoord->naam
			];

			if (!isset($avonden[date_format_intl($sessie->avond, self::FMT_DATE)])) {
				$avonden[date_format_intl($sessie->avond, self::FMT_DATE)] = $sessie->avond;
			}
		}

		return [
			'novieten' => array_values($eetplanFeut),
			'avonden' => array_values($avonden)
		];
	}

	/**
	 * @param string $avond
	 * @param string $lichting
	 *
	 * @return Eetplan[]
	 */
	public function maakEetplan($avond, $lichting) {
		$factory = new EetplanFactory();

		$bekenden = $this->eetplanBekendenRepository->getBekenden($lichting);
		$factory->setBekenden($bekenden);

		$bezocht = $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where("n.uid like :uid")
			->setParameter('uid', $lichting . '%')
			->getQuery()->getResult();
		$factory->setBezocht($bezocht);

		$novieten = $this->profielRepository->getNovieten($lichting);
		$factory->setNovieten($novieten);

		$huizen = ContainerFacade::getContainer()->get(WoonoordenRepository::class)->findBy(["eetplan" => true, "status" => GroepStatus::HT()]);
		$factory->setHuizen($huizen);

		return $factory->genereer($avond, true);
	}

	/**
	 * @param string $uid Uid van de feut
	 *
	 * @return Eetplan[]|false lijst van eetplansessies voor deze feut, gesorteerd op datum (oplopend)
	 */
	public function getEetplanVoorNoviet($uid) {
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.uid = :uid and e.avond is not null')
			->setParameter('uid', $uid)
			->orderBy('e.avond', 'ASC')
			->getQuery()->getResult();
	}

	/**
	 * @param int $woonoord_id Id van het huis
	 * @param string $lichting
	 *
	 * @return Eetplan[] lijst van eetplansessies voor dit huis, gegroepeerd op avond (oplopend)
	 */
	public function getEetplanVoorHuis($woonoord_id, $lichting) {
		/** @var Eetplan[] $sessies */
		$sessies = $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->join('e.woonoord', 'w')
			->where('n.uid like :uid and w.id = :woonoord_id and e.avond is not null')
			->setParameter('uid', $lichting . '%')
			->setParameter('woonoord_id', $woonoord_id)
			->orderBy('e.avond', 'ASC')
			->getQuery()->getResult();

		return array_reduce($sessies, function (array $accumulator, Eetplan $eetplan) {
			$accumulator[date_format_intl($eetplan->avond, self::FMT_DATE)][] = $eetplan;

			return $accumulator;
		}, []);
	}

	/**
	 * @param string $lichting
	 *
	 * @return Eetplan[]
	 */
	public function getBekendeHuizen($lichting) {
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('n.uid like :uid and e.avond is null')
			->setParameter('uid', $lichting . '%')
			->getQuery()->getResult();
	}

	/**
	 * @param string $avond
	 * @param string $lichting
	 */
	public function verwijderEetplan($avond, $lichting) {
		$alleEetplan = $this->getEetplanVoorAvond($avond, $lichting);

		foreach ($alleEetplan as $eetplan) {
			$this->remove($eetplan);
		}
	}

	/**
	 * @param string $avond
	 *
	 * @param $lichting
	 * @return Eetplan[]
	 */
	public function getEetplanVoorAvond($avond, $lichting) {
		return $this->createQueryBuilder('e')
			->join('e.noviet', 'n')
			->where('e.avond = :avond and n.uid like :uid')
			->setParameter('avond', $avond)
			->setParameter('uid', $lichting . '%')
			->getQuery()->getResult();
	}
}
