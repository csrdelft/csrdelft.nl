<?php

/**
 * EetplanFactory.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Verzorgt het aanmaken van een nieuw eetplan, gebasseerd op een bak met data
 */
class EetplanFactory {
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

    public function __construct() {

    }

    /**
     * @param EetplanBekenden[] $bekenden
     */
    public function setBekenden(array $bekenden) {
        $this->bekenden = array();
        foreach (bekenden as $eetplanBekenden) {
            $noviet1 = $eetplanBekenden->uid1;
            $noviet2 = $eetplanBekenden->uid2;
            $this->bekenden[$noviet1][$noviet2] = true;
            $this->bekenden[$noviet2][$noviet1] = true;
        }
    }

    /**
     * @param Eetplan[] $bezochten
     */
    public function setBezocht(array $bezochten) {
        $this->bezocht = array();
        $this->bezocht_ah = array();
        $this->bezocht_sh = array();
        foreach ($bezochten as $sessie) {
            $huis = $sessie->woonoord_id;
            $noviet = $sessie->uid;
            $avond = $sessie->avond;
            $this->bezocht[$huis][] = $noviet;
            $this->bezocht_sh[$noviet][$huis] = true;
            $this->bezocht_ah[$avond][$huis][] = $noviet;
            $this->sah[$noviet][$avond] = $huis;
            $this->ahs[$avond][$huis] = $noviet;
        }
    }
}