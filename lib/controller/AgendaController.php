<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\agenda\AgendaVerbergenRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\agenda\AgendaItemForm;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\Icon;
use CsrDelft\view\response\IcalResponse;
use DateInterval;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class AgendaController extends AbstractController
{
	const SECONDEN_IN_JAAR = 31557600;

	/**
	 * @param $refuuid
	 * @return Agendeerbaar|null
	 */
	private function getAgendaItemByUuid($refuuid)
	{
		$parts = explode('@', (string) $refuuid, 2);
		$module = explode('.', $parts[1], 2);
		$item = match ($module[0]) {
			'csrdelft' => $this->profielRepository->retrieveByUUID($refuuid),
			'maaltijd' => $this->maaltijdenRepository->retrieveByUUID($refuuid),
			'corveetaak' => $this->corveeTakenRepository->retrieveByUUID($refuuid),
			'activiteit' => $this->activiteitenRepository->retrieveByUUID($refuuid),
			'agendaitem' => $this->agendaRepository->retrieveByUUID($refuuid),
			default => throw new CsrException('invalid UUID'),
		};
		/** @var Agendeerbaar|null $item * */
		return $item;
	}

	/**
	 * @return mixed
	 */
	public function icalDate()
	{
		return str_replace(
			'-',
			'',
			str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c')))
		);
	}
}
