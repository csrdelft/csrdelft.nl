<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\ArchiefMaaltijdenRepository;
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

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		ArchiefMaaltijdenRepository $archiefMaaltijdenRepository,
		CorveeTakenRepository $corveeTakenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->archiefMaaltijdenRepository = $archiefMaaltijdenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
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
				$archief = $this->archiefMaaltijdenRepository->vanMaaltijd($maaltijd);
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
