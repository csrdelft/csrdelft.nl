<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\maalcie\ArchiefMaaltijd;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\ArchiefMaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class MaaltijdArchiefService
{
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var ArchiefMaaltijdenRepository
	 */
	private $archiefMaaltijdenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		ArchiefMaaltijdenRepository $archiefMaaltijdenRepository,
		CorveeTakenRepository $corveeTakenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->archiefMaaltijdenRepository = $archiefMaaltijdenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	public function vanMaaltijd(Maaltijd $maaltijd): ArchiefMaaltijd
	{
		$archief = new ArchiefMaaltijd();
		$archief->maaltijd_id = $maaltijd->maaltijd_id;
		$archief->titel = $maaltijd->titel;
		$archief->datum = $maaltijd->datum;
		$archief->tijd = $maaltijd->tijd;
		$archief->prijs = $maaltijd->getPrijs();
		$archief->aanmeldingen = '';
		foreach ($maaltijd->aanmeldingen as $aanmelding) {
			if (!$aanmelding->uid) {
				$archief->aanmeldingen .= 'gast';
			} else {
				$archief->aanmeldingen .= $aanmelding->uid;
			}
			if ($aanmelding->abonnementRepetitie) {
				$archief->aanmeldingen .= '_abo';
			}
			if ($aanmelding->door_uid !== null) {
				$archief->aanmeldingen .= '_' . $aanmelding->door_uid;
			}
			$archief->aanmeldingen .= ',';
		}

		return $archief;
	}

	/**
	 * @param int $van
	 * @param int $tot
	 * @return array
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function archiveerOudeMaaltijden($van, $tot)
	{
		if (!is_int($van) || !is_int($tot)) {
			throw new CsrException('Invalid timestamp: archiveerOudeMaaltijden()');
		}
		$errors = [];
		$maaltijden = $this->maaltijdenRepository->getMaaltijdenTussen($van, $tot);
		foreach ($maaltijden as $maaltijd) {
			try {
				$archief = $this->vanMaaltijd($maaltijd);
				$this->archiefMaaltijdenRepository->create($archief);
				if (
					$this->corveeTakenRepository->existMaaltijdCorvee(
						$maaltijd->maaltijd_id
					)
				) {
					FlashUtil::setFlashWithContainerFacade(
						DateUtil::dateFormatIntl(
							$maaltijd->getMoment(),
							DateUtil::DATETIME_FORMAT
						) . ' heeft nog gekoppelde corveetaken!',
						2
					);
				}
			} catch (CsrGebruikerException $e) {
				$errors[] = $e;
				FlashUtil::setFlashWithContainerFacade($e->getMessage(), -1);
			}
		}
		return [$errors, count($maaltijden)];
	}
}
