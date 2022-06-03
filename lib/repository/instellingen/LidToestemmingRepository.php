<?php

namespace CsrDelft\repository\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\instellingen\InstellingConfiguration;
use CsrDelft\common\instellingen\InstellingType;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\entity\LidToestemming;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Deze class houdt de toestemming bij voor een gebruiker.
 * In de sessie en in het profiel van leden.
 *
 * @method LidToestemming|null find($id, $lockMode = null, $lockVersion = null)
 * @method LidToestemming|null findOneBy(array $criteria, array $orderBy = null)
 * @method LidToestemming[]    findAll()
 * @method LidToestemming[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LidToestemmingRepository extends AbstractRepository
{
	use YamlInstellingen;

	const FIELD_MODULE = 'module';
	const FIELD_INSTELLING = 'instelling';
	const FIELD_UID = 'profiel';
	const MODULE_PROFIEL_LID = 'profiel_lid';
	const MODULE_PROFIEL_OUDLID = 'profiel_oudlid';
	const MODULE_PROFIEL = 'profiel';
	const MODULE_INTERN = 'intern';
	const MODULE_ALGEMEEN = 'algemeen';
	const FIELD_WAARDE = 'waarde';
	const MODULE_TOESTEMMING = 'toestemming';
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var LoginService
	 */
	private $loginService;

	/**
	 * @param ManagerRegistry $registry
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws LoaderLoadException
	 */
	public function __construct(ManagerRegistry $registry, RequestStack $requestStack, LoginService $loginService)
	{
		parent::__construct($registry, LidToestemming::class);

		$this->load('instellingen/toestemming.yaml', new InstellingConfiguration());
		$this->requestStack = $requestStack;
		$this->loginService = $loginService;
	}

	/**
	 * Geef de categorien waar een lid toestemming voor kan geven. Oudleden hebben minder gegevens dan leden.
	 *
	 * @param boolean $islid
	 * @return array
	 */
	public function getRelevantToestemmingCategories($islid)
	{
		$toestemmingen = [];

		if ($islid) {
			$toestemmingen[self::MODULE_PROFIEL_LID] = $this->getModuleKeys(self::MODULE_PROFIEL_LID);
		}

		$toestemmingen[self::MODULE_PROFIEL_OUDLID] = $this->getModuleKeys(self::MODULE_PROFIEL_OUDLID);
		$toestemmingen[self::MODULE_PROFIEL] = $this->getModuleKeys(self::MODULE_PROFIEL);
		$toestemmingen[self::MODULE_INTERN] = $this->getModuleKeys(self::MODULE_INTERN);

		return $toestemmingen;
	}

	protected function newToestemming($module, $id, $uid)
	{
		$toestemming = new LidToestemming();
		$toestemming->module = $module;
		$toestemming->instelling = $id;
		$toestemming->waarde = $this->getDefault($module, $id);
		$toestemming->profiel = ProfielRepository::get($uid);
		$this->_em->persist($toestemming);
		$this->_em->flush();
		return $toestemming;
	}

	public function toestemmingGegeven()
	{
		$requestUri = $this->requestStack->getCurrentRequest()->getRequestUri();
		$stopNag = $this->requestStack->getCurrentRequest()->getSession()->get('stop_nag', null);
		// Doe niet naggen op de privacy info pagina.
		if ($requestUri == '/privacy') {
			return true;
		}
		// Voorkom problemen tijdens opnieuw instellen wachtwoord
		if (str_starts_with($requestUri, '/wachtwoord')) {
			return true;
		}
		// Doe niet naggen voor een uur als een lid op annuleren heeft geklikt.
		if ($stopNag && $stopNag > time() - 3600) {
			return true;
		}

		$uid = $this->loginService->_getUid();

		$modules = [self::MODULE_ALGEMEEN, self::MODULE_INTERN, self::MODULE_PROFIEL];

		if ($this->count([self::FIELD_MODULE => $modules, self::FIELD_UID => $uid, self::FIELD_WAARDE => '']) != 0) {
			return false;
		}
		// Er is geen enkele selectie gemaakt
		if ($this->count([self::FIELD_UID => $uid]) == 0) {
			return false;
		}

		return true;
	}

	public function toestemming($profiel, $id, $cat = 'profiel', $except = P_LEDEN_MOD)
	{
		if (!$this->loginService->_mag(P_LEDEN_READ)) {
			return false;
		}

		if ($profiel->uid == $this->loginService->_getUid()) {
			return true;
		}

		if ($this->loginService->_mag($except)) {
			return true;
		}

		$toestemming = $this->findOneBy([self::FIELD_MODULE => $cat, self::FIELD_INSTELLING => $id, self::FIELD_UID => $profiel->uid]);

		if (!$toestemming) {
			return false;
		}

		return $toestemming->waarde == "ja";
	}

	public function toestemmingUid($uid, $id, $except = P_LEDEN_MOD)
	{
		if ($uid == $this->loginService->_getUid()) {
			return true;
		}

		if ($this->loginService->_mag($except)) {
			return true;
		}

		$toestemming = $this->findOneBy([self::FIELD_MODULE => self::MODULE_TOESTEMMING, self::FIELD_INSTELLING => $id, self::FIELD_UID => $uid]);

		if (!$toestemming) {
			return false;
		}

		return $toestemming->waarde == "ja";
	}

	public function getDescription($module, $id)
	{
		return $this->getField($module, $id, 'titel');
	}

	public function getType($module, $id)
	{
		if ($this->hasKey($module, $id)) {
			return $this->getField($module, $id, 'type');
		} else {
			return null;
		}
	}

	public function getTypeOptions($module, $id)
	{
		return $this->getField($module, $id, 'opties');
	}

	public function getDefault($module, $id)
	{
		return $this->getField($module, $id, 'default');
	}

	public function isValidValue($module, $id, $waarde)
	{
		$options = $this->getTypeOptions($module, $id);
		if ($this->getType($module, $id) == InstellingType::Enumeration) {
			if (in_array($waarde, $options)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getValue($module, $id)
	{
		return $this->getToestemming($module, $id)->waarde;
	}

	protected function getToestemming($module, $id, $uid = null)
	{
		if ($uid == null) {
			$uid = $this->loginService->_getUid();
		}
		$instelling = $this->findOneBy([self::FIELD_MODULE => $module, self::FIELD_INSTELLING => $id, self::FIELD_UID => $uid]);
		if ($this->hasKey($module, $id)) {
			if (!$instelling) {
				$instelling = $this->newToestemming($module, $id, $uid);
			}
			return $instelling;
		} else {
			if ($instelling) {
				// Haal niet-bestaande instelling uit de database
				$this->_em->remove($instelling);
				$this->_em->flush();
			}
			throw new CsrException(sprintf('Toestemming bestaat niet: "%s" module: "%s".', $id, $module));
		}
	}

	public function getToestemmingForIds($ids, $waardes = ['ja', 'nee'])
	{
		return $this->findBy([self::FIELD_INSTELLING => $ids, self::FIELD_WAARDE => $waardes], [self::FIELD_UID => 'ASC']);
	}

	/**
	 * @param null $uid Sla op voor uid
	 * @throws Exception
	 */
	public function saveForLid($uid = null)
	{
		// create matrix for sqlInsertMultiple
		foreach ($this->defaults as $module => $instellingen) {
			foreach ($instellingen as $id => $waarde) {
				if ($this->getType($module, $id) === InstellingType::Integer) {
					$filter = FILTER_SANITIZE_NUMBER_INT;
				} else {
					$filter = FILTER_SANITIZE_STRING;
				}
				$waarde = filter_input(INPUT_POST, $module . '_' . $id, $filter);
				if (!$this->isValidValue($module, $id, $waarde)) {
					continue;
				}
				$instelling = $this->getToestemming($module, $id, $uid);
				$instelling->waarde = (string)$waarde;
			}
		}
		$this->getEntityManager()->flush();
	}
}
