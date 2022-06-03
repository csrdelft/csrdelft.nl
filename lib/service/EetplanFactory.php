<?php

namespace CsrDelft\service;

use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\repository\ProfielRepository;

/**
 * EetplanFactory.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Verzorgt het aanmaken van een nieuw eetplan, gebasseerd op een bak met data
 */
class EetplanFactory
{
	/**
	 * Avond van deze nieuwe sessie
	 *
	 * @var string
	 */
	private $avond;
	/**
	 * Lijst van novieten die elkaar gezien hebben
	 *
	 * $bekenden[$noviet1][$noviet2] = true;
	 * $bekenden[$noviet2][$noviet1] = true;
	 *
	 * Beide manieren moeten gezet worden.
	 *
	 * @var array
	 */
	private $bekenden;
	/**
	 * Lijst van novieten die huizen bezocht hebben.
	 *
	 * $bezocht[$huis][] = $sjaars;
	 *
	 * @var array
	 */
	private $bezocht;
	/**
	 * Lijst van novieten die huizen op een bepaalde avond bezocht hebben.
	 *
	 * $bezocht_ah[$avond][$huis][] = $sjaars;
	 *
	 * @var array
	 */
	private $bezocht_ah;
	/**
	 * Lijst van novieten die huizen bezocht hebben (gebasseerd op noviet).
	 *
	 * $bezocht_sh[$sjaars][$huis] = true;
	 *
	 * @var array
	 */
	private $bezocht_sh;

	/**
	 * Sjaars - Avond - Huis
	 *
	 * $sah[$noviet][$avond][] = $huis;
	 *
	 * @var array
	 */
	private $sah;

	/**
	 * Avond - Huis - Sjaars
	 *
	 * $ahs[$avond][$huis][] = $sjaars;
	 *
	 * @var array
	 */
	private $ahs;

	/**
	 * @var Profiel[]
	 */
	private $novieten;

	/**
	 * @var Woonoord[]
	 */
	private $huizen;

	/**
	 * @param EetplanBekenden[] $bekenden
	 */
	public function setBekenden($bekenden)
	{
		$this->bekenden = [];

		foreach ($bekenden as $eetplanBekenden) {
			$noviet1 = $eetplanBekenden->noviet1->uid;
			$noviet2 = $eetplanBekenden->noviet2->uid;
			$this->bekenden[$noviet1][$noviet2] = true;
			$this->bekenden[$noviet2][$noviet1] = true;
		}
	}

	/**
	 * @param Eetplan[] $bezochten
	 */
	public function setBezocht($bezochten)
	{
		$this->bezocht = [];
		$this->bezocht_ah = [];
		$this->bezocht_sh = [];
		foreach ($bezochten as $sessie) {
			$huis = $sessie->woonoord->id;
			$noviet = $sessie->noviet->uid;
			$this->bezocht_sh[$noviet][$huis] = true;
		}
	}

	/**
	 * @param Profiel[] $novieten
	 */
	public function setNovieten($novieten)
	{
		$this->novieten = $novieten;
	}

	/**
	 * @param Woonoord[] $huizen
	 */
	public function setHuizen($huizen)
	{
		$this->huizen = $huizen;
	}

	/**
	 * Genereer een eetplansessie voor deze avond
	 *
	 * @param string $avond
	 * @param bool $random
	 * @return Eetplan[]
	 */
	public function genereer($avond, $random = false)
	{
		assert(isset($this->novieten), 'Veld novieten is niet gezet');
		assert(isset($this->huizen), 'Veld huizen is niet gezet');

		$eetplan = [];

		$aantalSjaars = count($this->novieten);
		$aantalHuizen = count($this->huizen) - 1;

		// $huis_index is het nummer van he thuis in $this->huizen
		$huisIndex = $random ? rand(0, $aantalHuizen) : 0;

		// Interne id, niet oplopend van huis
		$huisId = $this->huizen[$huisIndex]->id;

		$this->bezocht_ah[$avond] = [];

		foreach ($this->novieten as $noviet) {
			# wat foutmeldingen voorkomen
			if (!isset($this->ahs[$avond][$huisId])) {
				$this->ahs[$avond][$huisId] = [];
			}
			if (!isset($this->bekenden[$noviet->uid])) {
				$this->bekenden[$noviet->uid] = [];
			}
			if (!isset($this->bezocht_ah[$avond][$huisId])) {
				$this->bezocht_ah[$avond][$huisId] = [];
			}
			# we hebben nu een avond en een sjaars, nu nog een huis voor m vinden...
			# zolang
			# - deze sjaars dit huis al bezocht heeft, of
			# - in het huidige huis (huis_index) een sjaars zit die deze sjaars (noviet) al ontmoet heeft
			# - het huis nog niet aan zn max sjaars is voor deze avond
			# nemen we het volgende huis
			$startih = $huisIndex;
			# nieuw: begin met het max aantal sjaars per huis net iets te laag in te stellen, zodat
			# de huizen eerst goed vol komen, en daarna pas extra sjaars bij huizen
			$max = (int)floor($aantalSjaars / $aantalHuizen);
			$aantalHuizenVol = 0; # aantal huizen dat aan de max zit.
			while (isset($this->bezocht_sh[$noviet->uid][$huisId])
				|| count(array_intersect($this->ahs[$avond][$huisId], $this->bekenden[$noviet->uid])) > 0
				|| count($this->bezocht_ah[$avond][$huisId]) >= $max) {
				$huisIndex = $huisIndex % $aantalHuizen + 1;
				$huisId = $this->huizen[$huisIndex]->id;

				if ($huisIndex == $startih) {
					$max++;
				}
				if (!isset($this->ahs[$avond][$huisId])) {
					$this->ahs[$avond][$huisId] = [];
				}
				if (!isset($this->bezocht_ah[$avond][$huisId])) {
					$this->bezocht_ah[$avond][$huisId] = [];
				}

				# nieuw: als alle huizen zijn langsgelopen en ze allemaal max sjaars hebben
				# dan de max ophogen
				if (count($this->bezocht_ah[$avond][$huisId]) == $max) {
					$aantalHuizenVol++;
				}
				if ($aantalHuizenVol == $aantalHuizen) {
					$max++;
				}
			}

			# deze sjaars heeft op deze avond een huis gevonden
			$this->sah[$noviet->uid][$avond] = $huisId;
			# en gaat alle sjaars die op deze avond in dit huis zitten dat melden
			foreach ($this->ahs[$avond][$huisId] as $sjaars) {
				$this->bekenden[$noviet->uid][] = $sjaars; # alle sjaars in mijn seen
				$this->bekenden[$sjaars][] = $noviet->uid; # ik in alle sjaars' seen
			}
			$this->ahs[$avond][$huisId][] = $noviet->uid;
			# de sjaars heeft het huis bezocht
			$this->bezocht[$huisId][] = $noviet->uid;
			$this->bezocht_sh[$noviet->uid][$huisId] = true;
			$this->bezocht_ah[$avond][$huisId][] = $noviet->uid;

			# Maak een entity voor deze sessie
			$nieuweetplan = new Eetplan();
			$nieuweetplan->avond = date_create_immutable($avond);
			$nieuweetplan->noviet = $noviet;
			$nieuweetplan->woonoord = $this->huizen[$huisIndex];

			$eetplan[] = $nieuweetplan;

			# huis ophogen
			if ($random == 0) {
				$huisIndex = $huisIndex % $aantalHuizen + 1;
			} else {
				$huisIndex = rand(0, $aantalHuizen);
			}

			$huisId = $this->huizen[$huisIndex]->id;
		}

		return $eetplan;
	}
}
