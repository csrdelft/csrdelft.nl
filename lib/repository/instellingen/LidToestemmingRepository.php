<?php

namespace CsrDelft\repository\instellingen;

use CsrDelft\common\CsrException;
use CsrDelft\common\yaml\YamlInstellingen;
use CsrDelft\entity\LidToestemming;
use CsrDelft\model\instellingen\InstellingConfiguration;
use CsrDelft\model\instellingen\InstellingType;
use CsrDelft\model\security\LoginModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;


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
class LidToestemmingRepository extends ServiceEntityRepository {
	use YamlInstellingen;

	/**
	 * @param ManagerRegistry $registry
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws LoaderLoadException
	 */
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, LidToestemming::class);

		$this->load('instellingen/toestemming.yaml', new InstellingConfiguration());
	}

	/**
	 * Geef de categorien waar een lid toestemming voor kan geven. Oudleden hebben minder gegevens dan leden.
	 *
	 * @param boolean $islid
	 * @return array
	 */
	public function getRelevantToestemmingCategories($islid) {
		$instellingen = [];

		if ($islid) {
			$instellingen['profiel_lid'] = $this->getModuleKeys('profiel_lid');
		}

		$instellingen['profiel_oudlid'] = $this->getModuleKeys('profiel_oudlid');
		$instellingen['profiel'] = $this->getModuleKeys('profiel');
		$instellingen['intern'] = $this->getModuleKeys('intern');

		return $instellingen;
	}

	protected function newInstelling($module, $id, $uid = null) {
		$instelling = new LidToestemming();
		$instelling->module = $module;
		$instelling->instelling_id = $id;
		$instelling->waarde = $this->getDefault($module, $id);
		$instelling->uid = $uid ?? LoginModel::getUid();
		return $instelling;
	}

	public function toestemmingGegeven() {
		if ($_SERVER['REQUEST_URI'] == '/privacy') // Doe niet naggen op de privacy info pagina.
			return true;

		if (startsWith($_SERVER['REQUEST_URI'], '/wachtwoord')) // Voorkom problemen tijdens opnieuw instellen wachtwoord
			return true;

		if (isset($_SESSION['stop_nag']) && $_SESSION['stop_nag'] > time() - 3600) // Doe niet naggen voor een uur als een lid op annuleren heeft geklikt.
			return true;

		$uid = LoginModel::getUid();

		$modules = ['algemeen', 'intern', 'profiel'];

		if ($this->count(['module' => $modules, 'uid' => $uid, 'waarde' => '']) != 0)
			return false;

		if ($this->count(['uid' => $uid]) == 0) // Er is geen enkele selectie gemaakt
			return false;

		return true;
	}

	public function toestemming($profiel, $id, $cat = 'profiel', $except = P_LEDEN_MOD) {
		if (!LoginModel::mag(P_LEDEN_READ))
			return false;

		if ($profiel->uid == LoginModel::getUid())
			return true;

		if (LoginModel::mag($except))
			return true;

		$toestemming = $this->find(['module' => $cat, 'instelling_id' => $id, 'uid' => $profiel->uid]);

		if (!$toestemming)
			return false;

		return $toestemming->waarde == "ja";
	}

	public function toestemmingUid($uid, $id, $except = P_LEDEN_MOD) {
		if ($uid == LoginModel::getUid())
			return true;

		if (LoginModel::mag($except))
			return true;

		$toestemming = $this->find(['module' => 'toestemming', 'instelling_id' => $id, 'uid' => $uid]);

		if (!$toestemming)
			return false;

		return $toestemming->waarde == "ja";
	}

	public function getDescription($module, $id) {
		return $this->getField($module, $id, 'titel');
	}

	public function getType($module, $id) {
		if ($this->hasKey($module, $id)) {
			return $this->getField($module, $id, 'type');
		} else {
			return null;
		}
	}

	public function getTypeOptions($module, $id) {
		return $this->getField($module, $id, 'opties');
	}

	public function getDefault($module, $id) {
		return $this->getField($module, $id, 'default');
	}

	public function isValidValue($module, $id, $waarde) {
		$options = $this->getTypeOptions($module, $id);
		switch ($this->getType($module, $id)) {
			case InstellingType::Enumeration:
				if (in_array($waarde, $options)) {
					return true;
				}
				break;
		}
		return false;
	}

	/**
	 * @param string $module
	 * @param string $id
	 *
	 * @return string
	 */
	public function getValue($module, $id) {
		return $this->getInstelling($module, $id)->waarde;
	}

	protected function getInstelling($module, $id) {
		$instelling = $this->find(['module' => $module, 'instelling_id' => $id, 'uid' => LoginModel::getUid()]);
		if ($this->hasKey($module, $id)) {
			if (!$instelling) {
				$instelling = $this->newInstelling($module, $id);
			}
			return $instelling;
		} else {
			if ($instelling) {
				// Haal niet-bestaande instelling uit de database
				$entityManager = $this->getEntityManager();
				$entityManager->remove($instelling);
				$entityManager->flush();
			}
			throw new CsrException(sprintf('Toestemming bestaat niet: "%s" module: "%s".', $id, $module));
		}
	}

	public function getToestemmingForIds($ids, $waardes = ['ja', 'nee']) {
		return $this->findBy(['instelling_id' => $ids, 'waarde' => $waardes], ['uid' => 'ASC']);
	}

	/**
	 * @param null $uid Sla op voor uid
	 * @throws Exception
	 */
	public function save($uid = null) {
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
				$instelling = $this->newInstelling($module, $id, $uid);
				$instelling->waarde = $waarde;
				$this->getEntityManager()->persist($instelling);
			}
		}
		$this->getEntityManager()->flush();
	}
}
