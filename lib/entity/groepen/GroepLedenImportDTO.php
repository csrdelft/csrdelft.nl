<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\EntityRepository;

class GroepLedenImportDTO
{
	public $succes = false;
	private $lid;
	public $lidnaam = "";
	private $groep;
	public $groepnaam = "";
	public $opmerking = null;
	public $waarschuwingen = [];

	public function __construct(ProfielRepository $profielRepository, EntityRepository $groepRepository, array $regel)
	{
		// Check keys
		if (array_keys($regel) !== ['groepID', 'uid', 'opmerking']) {
			$this->succes = false;
			$this->waarschuwingen[] = 'Ongeldige kolommen';
			return;
		}

		// Haal lid op
		$this->lid = $profielRepository->find((strlen($regel['uid']) === 3 ? '0' : '') . $regel['uid']);
		if (!$this->lid) {
			$this->waarschuwingen[] = "Profiel {$regel['uid']} niet gevonden";
		} else {
			$this->lidnaam = $this->lid->getNaam();
		}

		// Haal groep op
		$this->groep = $groepRepository->find($regel['groepID']);
		if (!$this->groep) {
			$this->waarschuwingen[] = "Groep {$regel['groepID']} niet gevonden";
		} else {
			$this->groepnaam = $this->groep->naam;

			// Check rechten
			if (!$this->groep->mag(AccessAction::Beheren())) {
				$this->waarschuwingen[] = "Geen rechten om groep te beheren";
			}

			// Check of lid al in groep zit
			if ($this->groep->getLid($this->lid->uid)) {
				$this->waarschuwingen[] = "Lid zit al in groep";
			}
		}

		// Check opmerking
		if (!empty($regel['opmerking'])) {
			$this->opmerking = $regel['opmerking'];
			if (strlen($this->opmerking) > 255) {
				$this->waarschuwingen[] = "Opmerking is te lang";
			}
		}

		$this->succes = empty($this->waarschuwingen);
	}

	/**
	 * @param ProfielRepository $profielRepository
	 * @param EntityRepository $groepRepository
	 * @param array $data
	 * @return GroepLedenImportDTO[]
	 */
	public static function convert(ProfielRepository $profielRepository, EntityRepository $groepRepository, array $data): array
	{
		return array_map(function(array $regel) use ($profielRepository, $groepRepository) {
			return new GroepLedenImportDTO($profielRepository, $groepRepository, $regel);
		}, $data);
	}

	public function waarschuwing(): string {
		return join('<br />', $this->waarschuwingen);
	}

	public function aanmelden(GroepLidRepository $groepLidRepository): bool {
		if ($this->succes) {
			$groeplid = $groepLidRepository->nieuw($this->groep, $this->lid->uid);
			if ($this->opmerking) {
				$groeplid->opmerking = $this->opmerking;
			}

			$groepLidRepository->save($groeplid);
			return true;
		}

		return false;
	}
}
