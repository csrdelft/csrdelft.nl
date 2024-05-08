<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\service\maalcie\MaaltijdAanmeldingenService;
use CsrDelft\service\maalcie\MaaltijdenService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

/**
 * Geeft een maaltijdketzer weer met maaltijdgegevens, aantal aanmeldingen en een aanmeldknopje.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [maaltijd=next]
 * @example [maaltijd=1234]
 * @example [maaltijd]next[/maaldijd]
 * @example [maaltijd]123[/maaltijd]
 */
class BbMaaltijd extends BbTag
{
	/**
	 * @var Maaltijd[]
	 */
	private $maaltijden;
	/**
	 * @var string
	 */
	private $id;

	public function __construct(
		private readonly Environment $twig,
		private readonly Security $security,
		private readonly MaaltijdenService $maaltijdenService,
		private readonly MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		private readonly MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		private readonly MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository
	) {
	}

	public static function getTagName()
	{
		return 'maaltijd';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	public function renderLight()
	{
		$maaltijd = $this->maaltijden[0];
		$url = $maaltijd->getUrl() . '#' . $maaltijd->maaltijd_id;
		return BbHelper::lightLinkBlock(
			'maaltijd',
			$url,
			$maaltijd->titel,
			DateUtil::dateFormatIntl(
				$maaltijd->getMoment(),
				DateUtil::DATETIME_FORMAT
			)
		);
	}

	public function render()
	{
		$result = '<div class="my-3 p-3 maaltijdketzer-wrapper rounded">';
		foreach ($this->maaltijden as $maaltijd) {
			// Aanmeldingen
			$aanmeldingen = $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid(
				[$maaltijd->maaltijd_id => $maaltijd],
				LoginService::getUid()
			);
			if (empty($aanmeldingen)) {
				$aanmelding = null;
			} else {
				$aanmelding = $aanmeldingen[$maaltijd->maaltijd_id];
			}

			// Beoordelingen ophalen
			$kwaliteit = null;
			$kwantiteit = null;
			if ($maaltijd->getEindMoment() < date_create_immutable()) {
				$beoordeling = $this->maaltijdBeoordelingenRepository->find([
					'maaltijd_id' => $maaltijd->maaltijd_id,
					'uid' => LoginService::getUid(),
				]);
				if (!$beoordeling) {
					$beoordeling = $this->maaltijdBeoordelingenRepository->nieuw(
						$maaltijd
					);
				}
				$kwantiteit = (new MaaltijdKwantiteitBeoordelingForm(
					$maaltijd,
					$beoordeling
				))->getHtml();
				$kwaliteit = (new MaaltijdKwaliteitBeoordelingForm(
					$maaltijd,
					$beoordeling
				))->getHtml();
			}

			$result .= $this->twig->render('maaltijden/bb.html.twig', [
				'maaltijd' => $maaltijd,
				'kwantiteit' => $kwantiteit,
				'kwaliteit' => $kwaliteit,
				'aanmelding' => $aanmelding,
				'border' => count($this->maaltijden) > 1,
			]);
		}
		if (count($this->maaltijden) > 1 && $this->id !== 'beoordeling') {
			$result .=
				'<div class="d-block mt-3 text-end"><a href="/maaltijden/ketzer">Alle maaltijden</a></div>';
		}
		return $result . '</div>';
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->id = $this->readMainArgument($arguments);
		$this->maaltijden = [];
		foreach ($this->getMaaltijd($this->id) as $maaltijd) {
			if ($maaltijd != null) {
				$this->maaltijden[] = $maaltijd;
			}
		}
	}

	/**
	 * @param string|null $mid
	 * @return array
	 * @throws BbException
	 */
	private function getMaaltijd($mid): array
	{
		// @TODO clean up this ugly code
		$maaltijd2 = null;

		try {
			if (
				$mid === 'next' ||
				$mid === 'eerstvolgende' ||
				$mid === 'next2' ||
				$mid === 'eerstvolgende2'
			) {
				$maaltijden = $this->maaltijdenService->getKomendeMaaltijdenVoorLid(
					LoginService::getProfiel()
				); // met filter
				$aantal = sizeof($maaltijden);
				if ($aantal < 1) {
					throw new BbException(
						'<div class="bb-block bb-maaltijd">Geen aankomende maaltijd.</div>'
					);
				}
				$maaltijd = reset($maaltijden);
				if (str_ends_with($mid, '2') && $aantal >= 2) {
					unset($maaltijden[$maaltijd->maaltijd_id]);
					$maaltijd2 = reset($maaltijden);
				}
			} elseif ($mid === 'beoordeling') {
				$timestamp = date_create_immutable(
					InstellingUtil::instelling('maaltijden', 'beoordeling_periode')
				);
				$recent = $this->maaltijdAanmeldingenService->getRecenteAanmeldingenVoorLid(
					LoginService::getUid(),
					$timestamp
				);
				$recent = array_slice(array_map(fn($m) => $m->maaltijd, $recent), -2);
				if (count($recent) === 0) {
					throw new BbException('');
				}
				$maaltijd = array_values($recent)[0];
				if (count($recent) > 1) {
					$maaltijd2 = array_values($recent)[1];
				}
			} elseif (preg_match('/\d+/', (string) $mid)) {
				$maaltijd = $this->maaltijdenService->getMaaltijdVoorKetzer((int) $mid); // met filter

				if (!$maaltijd) {
					throw new BbException('');
				}
			}
		} catch (CsrException $e) {
			if (str_contains($e->getMessage(), 'Not found')) {
				throw new BbException(
					'<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' .
						htmlspecialchars((string) $mid) .
						'</div>'
				);
			}
			throw new BbException($e->getMessage());
		}
		if (!isset($maaltijd)) {
			throw new BbException(
				'<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' .
					htmlspecialchars((string) $mid) .
					'</div>'
			);
		}
		return [$maaltijd, $maaltijd2];
	}

	public function getId()
	{
		return $this->id;
	}
}
