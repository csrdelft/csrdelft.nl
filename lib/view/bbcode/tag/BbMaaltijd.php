<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\BbHelper;

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
class BbMaaltijd extends BbTag {

	/**
	 * @var Maaltijd[]
	 */
	private $maaltijden;

	public static function getTagName() {
		return 'maaltijd';
	}

	public function isAllowed() {
		return LoginModel::mag(P_LOGGED_IN);
	}

	public function renderLight() {
		$maaltijd = $this->maaltijden[0];
		$url = $maaltijd->getUrl() . '#' . $maaltijd->maaltijd_id;
		return BbHelper::lightLinkBlock('maaltijd', $url, $maaltijd->titel, $maaltijd->datum . ' ' . $maaltijd->tijd);
	}

	public function render() {
		$result = '<div class="my-3 p-3 bg-white rounded shadow-sm">';
		foreach ($this->maaltijden as $maaltijd) {
			$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorLid(array($maaltijd->maaltijd_id => $maaltijd), LoginModel::getUid());
			if (empty($aanmeldingen)) {
				$aanmelding = null;
			} else {
				$aanmelding = $aanmeldingen[$maaltijd->maaltijd_id];
			}
			$result .= view('maaltijden.bb', [
				'maaltijd' => $maaltijd,
				'aanmelding' => $aanmelding,
				'border' => count($this->maaltijden) > 1
			])->getHtml();
		}
		if (count($this->maaltijden) > 1) {
			$result .= '<div class="d-block mt-3 text-right"><a href="/maaltijden/ketzer">Alle maaltijden</a></div>';
		}
		return $result . '</div>';
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = []) {
		$this->readMainArgument($arguments);
		$this->maaltijden = [];
		foreach ($this->getMaaltijd($this->content) as $maaltijd) {
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
	private function getMaaltijd($mid): array {
		// @TODO clean up this ugly code
		$maaltijd2 = null;

		try {
			if ($mid === 'next' || $mid === 'eerstvolgende' || $mid === 'next2' || $mid === 'eerstvolgende2') {
				$maaltijden = MaaltijdenModel::instance()->getKomendeMaaltijdenVoorLid(LoginModel::getUid()); // met filter
				$aantal = sizeof($maaltijden);
				if ($aantal < 1) {
					throw new BbException('<div class="bb-block bb-maaltijd">Geen aankomende maaltijd.</div>');
				}
				$maaltijd = reset($maaltijden);
				if (endsWith($mid, '2') && $aantal >= 2) {
					unset($maaltijden[$maaltijd->maaltijd_id]);
					$maaltijd2 = reset($maaltijden);
				}
			} elseif (preg_match('/\d+/', $mid)) {
				$maaltijd = MaaltijdenModel::instance()->getMaaltijdVoorKetzer((int)$mid); // met filter
				if (!$maaltijd) {
					throw new BbException('');
				}
			}
		} catch (CsrException $e) {
			if (strpos($e->getMessage(), 'Not found') !== false) {
				throw new BbException('<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' . htmlspecialchars($mid) . '</div>');
			}
			throw new BbException($e->getMessage());
		}
		if (!isset($maaltijd)) {
			throw new BbException('<div class="bb-block bb-maaltijd">Maaltijd niet gevonden: ' . htmlspecialchars($mid) . '</div>');
		}
		return array($maaltijd, $maaltijd2);
	}
}
