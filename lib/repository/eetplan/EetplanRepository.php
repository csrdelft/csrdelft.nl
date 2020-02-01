<?php

namespace CsrDelft\repository\eetplan;

use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\OrmTrait;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\EetplanFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Verzorgt het opvragen van eetplangegevens
 *
 * @method Eetplan[]    ormFind($criteria = null, $criteria_params = [], $group_by = null, $order_by = null, $limit = null, $start = 0)
 * @method Eetplan|null doctrineFind($id, $lockMode = null, $lockVersion = null)
 * @method Eetplan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Eetplan[]    findAll()
 * @method Eetplan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EetplanRepository extends ServiceEntityRepository {
	use OrmTrait;
	const FMT_DATE = "d-m-Y";

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

	/**
	 * Haal alle avonden op die voor deze lichting gelden.
	 *
	 * @param $lichting
	 *
	 * @return Eetplan[] Lijst met eetplan objecten met alleen een avond.
	 */
	public function getAvonden($lichting) {
		return $this->ormFind('uid LIKE ? AND avond <> "0000-00-00"', [$lichting . "%"], 'avond');
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
		// Avond 0000-00-00 wordt gebruikt voor novieten die huizen kennen
		// Orderen bij avond, zodat de avondvolgorde per noviet klopt
		/** @var Eetplan[] $eetplan */
		$eetplan = $this->ormFind('uid LIKE ? AND avond <> "0000-00-00"', [$lichting . "%"], null, 'avond');
		$eetplanFeut = [];
		$avonden = [];
		foreach ($eetplan as $sessie) {
			if (!isset($eetplanFeut[$sessie->uid])) {
				$eetplanFeut[$sessie->uid] = [
					'avonden' => [],
					'uid' => $sessie->uid,
					'naam' => $sessie->noviet->getNaam()
				];
			}

			$eetplanFeut[$sessie->uid]['avonden'][] = [
				'datum' => $sessie->avond,
				'woonoord_id' => $sessie->woonoord_id,
				'woonoord' => $sessie->getWoonoord()->naam
			];

			if (!isset($avonden[$sessie->avond->format(self::FMT_DATE)])) {
				$avonden[$sessie->avond->format(self::FMT_DATE)] = $sessie->avond;
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

		$bezocht = $this->ormFind("uid like ?", [$lichting . "%"]);
		$factory->setBezocht($bezocht);

		$novieten = $this->profielRepository->ormFind("uid LIKE ? AND status = 'S_NOVIET'", [$lichting . "%"]);
		$factory->setNovieten($novieten);

		$huizen = WoonoordenModel::instance()->find("eetplan = true AND status = 'ht'")->fetchAll();
		$factory->setHuizen($huizen);

		return $factory->genereer($avond, true);
	}

	/**
	 * @param string $uid Uid van de feut
	 *
	 * @return Eetplan[]|false lijst van eetplansessies voor deze feut, gesorteerd op datum (oplopend)
	 */
	public function getEetplanVoorNoviet($uid) {
		return $this->ormFind('uid = ? AND avond <> "0000-00-00"', [$uid], null, 'avond');
	}

	/**
	 * @param int $woonoord_id Id van het huis
	 * @param string $lichting
	 *
	 * @return Eetplan[] lijst van eetplansessies voor dit huis, gegroepeerd op avond (oplopend)
	 */
	public function getEetplanVoorHuis($id, $lichting) {
		$sessies = $this->ormFind('uid LIKE ? AND woonoord_id = ? AND avond <> "0000-00-00"', [$lichting . "%", $id], null, 'avond');

		return array_reduce($sessies, function (array $accumulator, Eetplan $eetplan) {
			$accumulator[$eetplan->avond->format(self::FMT_DATE)][] = $eetplan;

			return $accumulator;
		}, []);
	}

	/**
	 * @param string $lichting
	 *
	 * @return Eetplan[]
	 */
	public function getBekendeHuizen($lichting) {
		return $this->ormFind('uid LIKE ? AND avond = "0000-00-00"', [$lichting . "%"]);
	}

	/**
	 * @param string $avond
	 * @param string $lichting
	 */
	public function verwijderEetplan($avond, $lichting) {
		$alleEetplan = $this->getEetplanVoorAvond($avond, $lichting);

		foreach ($alleEetplan as $eetplan) {
			$this->delete($eetplan);
		}
	}

	/**
	 * @param string $avond
	 *
	 * @return Eetplan[]
	 */
	public function getEetplanVoorAvond($avond, $lichting) {
		return $this->ormFind('avond = ? AND uid LIKE ?', [$avond, $lichting . "%"]);
	}
}
