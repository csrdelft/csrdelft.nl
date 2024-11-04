<?php

namespace CsrDelft\repository;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\LDAP;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielCreateLogGroup;
use CsrDelft\model\entity\profiel\ProfielLogValueChange;
use CsrDelft\model\entity\profiel\ProfielUpdateLogGroup;
use CsrDelft\service\security\LoginService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\Security;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method Profiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profiel[]    findAll()
 * @method Profiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfielRepository extends AbstractRepository
{


	/**
	 * @param null|string $uid
	 */
	public static function changelog(array $diff, string|null $uid)
	{
		if (empty($diff)) {
			return null;
		}
		$changes = [];
		foreach ($diff as $change) {
			$changes[] = new ProfielLogValueChange(
				$change->property,
				$change->old_value,
				$change->new_value
			);
		}
		return new ProfielUpdateLogGroup($uid, date_create_immutable(), $changes);
	}

	/**
	 * @param string $uid
	 * @return Profiel|false
	 * @deprecated Gebruik ProfielRepository::find($uid)
	 */
	public static function get($uid)
	{
		if ($uid == null || !ctype_alnum($uid) || strlen($uid) != 4) {
			return null;
		}

		$model = ContainerFacade::getContainer()->get(ProfielRepository::class);

		return $model->find($uid);
	}

	/**
	 * @param $uid
	 * @param $vorm
	 *
	 * @return string|null
	 *
	 * @deprecated Gebruik Profiel::getNaam($vorm)
	 *
	 * @psalm-param 'civitas'|'user'|'volledig' $vorm
	 */
	public static function getNaam(string $uid, string $vorm = 'civitas')
	{
		$profiel = static::get($uid);
		if (!$profiel) {
			return null;
		}
		return $profiel->getNaam($vorm);
	}

	/**
	 * @param $uid
	 * @param $vorm
	 *
	 * @return string|null
	 *
	 * @deprecated Gebruik Profiel::getLink($vorm)
	 *
	 * @psalm-param 'civitas'|'volledig' $vorm
	 */
	public static function getLink(string $uid, string $vorm = 'civitas')
	{
		$profiel = static::get($uid);
		if (!$profiel) {
			return null;
		}
		return $profiel->getLink($vorm);
	}

	/**
	 * @param null|scalar $uid
	 *
	 * @return bool
	 *
	 * @deprecated Doe een null check op ProfielRepository::find($uid)
	 */
	public static function existsUid($uid)
	{
		if (!ctype_alnum((string) $uid) || strlen((string) $uid) != 4) {
			return false;
		}
		$model = ContainerFacade::getContainer()->get(ProfielRepository::class);
		return $model->find($uid) !== null;
	}

	public function existsDuck($duck)
	{
		return count($this->findBy(['duckname' => $duck])) !== 0;
	}

	public function nieuw(int|string $lidjaar, string $lidstatus)
	{
		$user = $this->security->getUser();

		// Create kan door x999 gedaan worden
		$logUsername =
			$user == null ? LoginService::UID_EXTERN : $user->getUsername();
		$profiel = new Profiel();
		$profiel->lidjaar = $lidjaar;
		$profiel->status = $lidstatus;
		$profiel->ontvangtcontactueel = OntvangtContactueel::Nee();
		$profiel->changelog = [
			new ProfielCreateLogGroup($logUsername, new DateTime()),
		];
		return $profiel;
	}

	/**
	 * @param Profiel $profiel
	 * @throws NonUniqueResultException
	 */
	public function create(Profiel $profiel)
	{
		// Lichting zijn de laatste 2 cijfers van lidjaar
		$jj = substr($profiel->lidjaar, 2, 2);
		try {
			$laatste_uid = $this->createQueryBuilder('p')
				->select('MAX(p.uid)')
				->where('p.uid LIKE :jj')
				->setParameter('jj', $jj . '__')
				->getQuery()
				->getSingleScalarResult();
			$volgnummer = intval(substr($laatste_uid, 2, 2)) + 1;
		} catch (NoResultException) {
			$volgnummer = 1;
		}
		$profiel->uid = $jj . sprintf('%02d', $volgnummer);

		$this->save($profiel);
	}

	/**
	 * @param Profiel $profiel
	 */
	public function update(Profiel $profiel)
	{
		try {
			$this->save_ldap($profiel);
		} catch (Exception $e) {
			FlashUtil::setFlashWithContainerFacade($e->getMessage(), -1); //TODO: logging
		}
		$this->save($profiel);
	}

	/**
	 * Geef een lidjaar mee om alleen novieten van een specifiek lidjaar op te halen.
	 *
	 * @param null $lidjaar
	 * @return int|mixed|string
	 */
	public function getNovietenVanLaatsteLidjaar($lidjaar = null)
	{
		if (empty($lidjaar)) {
			return $this->createQueryBuilder('p')
				->where('p.status = :status')
				->setParameter('status', LidStatus::Noviet)
				->getQuery()
				->getResult();
		}

		return $this->createQueryBuilder('p')
			->where('p.status = :status and p.lidjaar = :lidjaar')
			->setParameter('lidjaar', $lidjaar)
			->setParameter('status', LidStatus::Noviet)
			->getQuery()
			->getResult();
	}

	/**
	 * @param string[] $toegestaan
	 *
	 * @return Profiel[]
	 *
	 * @psalm-param array<string> $toegestaan
	 */
	public function findByLidStatus(array $toegestaan)
	{
		return $this->createQueryBuilder('p')
			->where('p.status in (:toegestaan)')
			->setParameter('toegestaan', $toegestaan)
			->getQuery()
			->getResult();
	}

	public function setEetwens(Profiel $profiel, $eetwens)
	{
		if ($profiel->eetwens === $eetwens) {
			return;
		}
		$profiel->eetwens = $eetwens;
		$this->update($profiel);
	}
}
