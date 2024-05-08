<?php

namespace CsrDelft\service;

use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\eetplan\EetplanBekendenRepository;
use CsrDelft\repository\eetplan\EetplanRepository;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\repository\ProfielRepository;

class EetplanService 
{
    /** @var EetplanRepository */
    private $eetplanRepository;
    /** @var EetplanBekendenRepository */
    private $eetplanBekendenRepository;
    /** @var WoonoordenRepository */
    private $woonoordenRepository;
    /** @var ProfielRepository */
    private $profielRepository;

    public function __construct(
        EetplanRepository $eetplanRepository, 
        EetplanBekendenRepository $eetplanBekendenRepository, 
        WoonoordenRepository $woonoordenRepository,
        ProfielRepository $profielRepository
    ) {
        $this->eetplanRepository = $eetplanRepository;
        $this->eetplanBekendenRepository = $eetplanBekendenRepository;
        $this->woonoordenRepository = $woonoordenRepository;
        $this->profielRepository = $profielRepository;
    }

    /**
	 * @param string $avond
	 * @param integer $lidjaar
	 *
	 * @return Eetplan[]
	 */
	public function maakEetplan($avond, $lidjaar): array
	{
		$factory = new EetplanFactory();

		$bekenden = $this->eetplanBekendenRepository->getBekendenVoorLidjaar(
			$lidjaar
		);
		$factory->setBekenden($bekenden);

		$bezocht = $this->eetplanRepository->getBezocht($lidjaar);
		$factory->setBezocht($bezocht);

		$novieten = $this->profielRepository->getNovietenVanLaatsteLidjaar(
			$lidjaar
		);
		$factory->setNovieten($novieten);

		$huizen = $this->woonoordenRepository->findBy(['eetplan' => true, 'status' => GroepStatus::HT()]);
		$factory->setHuizen($huizen);

		return $factory->genereer($avond, true);
	}
}
