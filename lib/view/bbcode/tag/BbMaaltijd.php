<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use Symfony\Component\Security\Core\Security;
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
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var MaaltijdBeoordelingenRepository
	 */
	private $maaltijdBeoordelingenRepository;
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		Environment $twig,
		Security $security,
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdBeoordelingenRepository $maaltijdBeoordelingenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdBeoordelingenRepository = $maaltijdBeoordelingenRepository;
		$this->twig = $twig;
		$this->security = $security;
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
			date_format_intl($maaltijd->getMoment(), DATETIME_FORMAT)
		);
	}

	public function render()
	{
		$result = '<div class="my-3 p-3 maaltijdketzer-wrapper rounded shadow-sm">';
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
			if ($maaltijd->getEindMoment() < time()) {
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
				$maaltijden = $this->maaltijdenRepository->getKomendeMaaltijdenVoorLid(
					LoginService::getUid()
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
					instelling('maaltijden', 'beoordeling_periode')
				);
				$recent = $this->maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid(
					LoginService::getUid(),
					$timestamp
				);
				$recent = array_slice(
					array_map(function ($m) {
						return $m->maaltijd;
					}, $recent),
					-2
				);
				if (count($recent) === 0) {
					throw new BbException('');
				}
				$maaltijd = array_values($recent)[0];
				if (count($recent) > 1) {
					$maaltijd2 = array_values($recent)[1];
				}
			} elseif (preg_match('/\d+/', $mid)) {
				$maaltijd = $this->maaltijdenRepository->getMaaltijdVoorKetzer(
					(int) $mid
				); // met filter

				if (!$maaltijd) {
					throw new BbException('');
				}
			}
		} catch (CsrException $e) {
			if (strpos($e->getMessage(), 'Not found') !== false) {
				throw new BbException(
					'<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' .
						htmlspecialchars($mid) .
						'</div>'
				);
			}
			throw new BbException($e->getMessage());
		}
		if (!isset($maaltijd)) {
			throw new BbException(
				'<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' .
					htmlspecialchars($mid) .
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
