<?php

namespace App\Eetplan\Services;
use App\Eetplan\Contracts\EetplanContract;
use App\Eetplan\Models\Eetplan;
use App\Eetplan\Models\EetplanBekenden;
use App\Models\Profiel;
use CsrDelft\model\entity\groepen\Woonoord;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/04/2018
 */
class EetplanService implements EetplanContract
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
     * Genereer een eetplansessie voor deze avond
     *
     * @param string $avond
     * @param Profiel[] $novieten
     * @param Woonoord[] $huizen
     * @param EetplanBekenden[] $bekenden
     * @param Eetplan[] $bezochten
     * @param bool $random
     * @return Eetplan[]
     */
    public function genereer($avond, array $novieten, array $huizen, array $bekenden, array $bezochten, $random = false) {
        $this->setNovieten($novieten);
        $this->setHuizen($huizen);
        $this->setBekenden($bekenden);
        $this->setBezocht($bezochten);

        $eetplan = array();

        $aantal_sjaars = count($novieten);
        $aantal_huizen = count($huizen) - 1;

        // $huis_index is het nummer van he thuis in $this->huizen
        if ($random == false) {
            $huis_index = 0;
        } else {
            $huis_index = rand(0, $aantal_huizen);
        }

        // Interne id, niet oplopend van huis
        $huis_id = $this->huizen[$huis_index]->id;

        $this->bezocht_ah[$avond] = array();

        foreach ($this->novieten as $noviet) {
            $uid = $noviet->uid;
            # wat foutmeldingen voorkomen
            if (!isset($this->ahs[$avond][$huis_id]))
                $this->ahs[$avond][$huis_id] = array();
            if (!isset($this->bekenden[$uid]))
                $this->bekenden[$uid] = array();
            if (!isset($this->bezocht_ah[$avond][$huis_id]))
                $this->bezocht_ah[$avond][$huis_id] = array();
            # we hebben nu een avond en een sjaars, nu nog een huis voor m vinden...
            # zolang
            # - deze sjaars dit huis al bezocht heeft, of
            # - in het huidige huis (huis_index) een sjaars zit die deze sjaars (noviet) al ontmoet heeft
            # - het huis nog niet aan zn max sjaars is voor deze avond
            # nemen we het volgende huis
            $startih = $huis_index;
            # nieuw: begin met het max aantal sjaars per huis net iets te laag in te stellen, zodat
            # de huizen eerst goed vol komen, en daarna pas extra sjaars bij huizen
            $max = (int)floor($aantal_sjaars / $aantal_huizen);
            $nofm = 0; # aantal huizen dat aan de max zit.
            while (isset($this->bezocht_sh[$uid][$huis_id])
                or count(array_intersect($this->ahs[$avond][$huis_id], $this->bekenden[$uid])) > 0
                or count($this->bezocht_ah[$avond][$huis_id]) >= $max) {
                $huis_index = $huis_index % $aantal_huizen + 1;
                $huis_id = $this->huizen[$huis_index]->id;
                if ($huis_index == $startih) {
                    $max++; #die ('whraagh!!!');
                }
                if (!isset($this->ahs[$avond][$huis_id])) {
                    $this->ahs[$avond][$huis_id] = array();
                }
                if (!isset($this->bezocht_ah[$avond][$huis_id]))
                    $this->bezocht_ah[$avond][$huis_id] = array();

                # nieuw: als alle huizen zijn langsgelopen en ze allemaal max sjaars hebben
                # dan de max ophogen
                if (count($this->bezocht_ah[$avond][$huis_id]) == $max) {
                    $nofm++;
                }
                if ($nofm == $aantal_huizen) {
                    $max++;
                }
            }

            # deze sjaars heeft op deze avond een huis gevonden
            $this->sah[$uid][$avond] = $huis_id;
            # en gaat alle sjaars die op deze avond in dit huis zitten dat melden
            foreach ($this->ahs[$avond][$huis_id] as $sjaars) {
                $this->bekenden[$uid][] = $sjaars; # alle sjaars in mijn seen
                $this->bekenden[$sjaars][] = $uid; # ik in alle sjaars' seen
            }
            $this->ahs[$avond][$huis_id][] = $uid;
            # de sjaars heeft het huis bezocht
            $this->bezocht[$huis_id][] = $uid;
            $this->bezocht_sh[$uid][$huis_id] = true;
            $this->bezocht_ah[$avond][$huis_id][] = $uid;

            # Maak een entity voor deze sessie
            $nieuweetplan = new Eetplan();
            $nieuweetplan->avond = $avond;
            $nieuweetplan->uid = $uid;
            $nieuweetplan->woonoord_id = $huis_id;

            $eetplan[] = $nieuweetplan;

            # huis ophogen
            if ($random == 0)
                $huis_index = $huis_index % $aantal_huizen + 1;
            else
                $huis_index = rand(0, $aantal_huizen);

            $huis_id = $this->huizen[$huis_index]->id;
        }

        return $eetplan;
    }

    /**
     * @param EetplanBekenden[] $bekenden
     */
    private function setBekenden(array $bekenden) {
        $this->bekenden = array();
        foreach ($bekenden as $eetplanBekenden) {
            $noviet1 = $eetplanBekenden->uid1;
            $noviet2 = $eetplanBekenden->uid2;
            $this->bekenden[$noviet1][$noviet2] = true;
            $this->bekenden[$noviet2][$noviet1] = true;
        }
    }

    /**
     * @param Eetplan[] $bezochten
     */
    private function setBezocht(array $bezochten) {
        $this->bezocht = array();
        $this->bezocht_ah = array();
        $this->bezocht_sh = array();
        foreach ($bezochten as $sessie) {
            $huis = $sessie->woonoord_id;
            $noviet = $sessie->uid;
            $this->bezocht_sh[$noviet][$huis] = true;
        }
    }

    /**
     * @param Profiel[] $novieten
     */
    private function setNovieten(array $novieten) {
        $this->novieten = $novieten;
    }

    /**
     * @param Woonoord[] $huizen
     */
    private function setHuizen(array $huizen) {
        $this->huizen = $huizen;
    }
}