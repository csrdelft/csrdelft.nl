<?php

require_once 'model/entity/Eetplan.class.php';
require_once 'model/entity/EetplanBekenden.class.php';

/**
 * EetplanModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * 
 * Verzorgt het opvragen van eetplangegevens
 */
class EetplanModel extends PersistenceModel {

    const orm = 'Eetplan';

    private $lichting;
    private $bekendenModel;

    /**
     * EetplanModel constructor.
     * @param string $lichting Lichting om eetplan voor op te halen, 2 cijfers.
     */
    public function __construct($lichting)
    {
        parent::__construct();
        $this->lichting = $lichting;
        $this->bekendenModel = new EetplanBekendenModel($lichting);
    }

    /**
     * Haal alle avonden op die voor deze lichting gelden.
     *
     * @return Eetplan[] Lijst met sparse(!) eetplan objecten met alleen een avond.
     */
    public function getAvonden() {
        return $this->findSparse(array('avond'), 'uid LIKE ?', array("$this->lichting%"), 'avond')->fetchAll();
    }

    /**
     * Haal het volledige eetplan op (voor de huidige lichting)
     *
     * Uitvoer is een array met 'uid' => [Eetplan, Eetplan, ...]
     *
     * @return array Het eetplan
     */
    public function getEetplan() {

        $eetplan = $this->find('uid LIKE ?', array("$this->lichting%"));
        $eetplanFeut = array();
        foreach ($eetplan as $sessie) {
            if (!isset($eetplanFeut[$sessie->uid])) {
                $eetplanFeut[$sessie->uid] = array();
            }
            $eetplanFeut[$sessie->uid][] = $sessie;
        }

        return $eetplanFeut;
    }

    /**
     * @param $uid string Uid van de feut
     * @return Eetplan[] lijst van eetplansessies voor deze feut, gesorteerd op datum (oplopend)
     */
    public function getEetplanVoorNoviet($uid) {
        return $this->find('uid = ?', array($uid), null, 'avond')->fetchAll();
    }

    /**
     * @param $id int Id van het huis
     * @return Eetplan[] lijst van eetplansessies voor dit huis, gesorteerd op datum (oplopend)
     */
    public function getEetplanVoorHuis($id) {
        return $this->find('uid LIKE ? AND woonoord_id = ?', array("$this->lichting%", $id), null, 'avond')->fetchAll();
    }

    public function getBekendeHuizen() {
        return $this->find('uid LIKE ? AND avond = DATE(0)', array("$this->lichting%"))->fetchAll();
    }

    public function getBekendenModel() {
        return $this->bekendenModel;
    }
}

class EetplanBekendenModel extends PersistenceModel {

    private $lichting;

    const orm = "EetplanBekenden";

    /**
     * EetplanBekenden constructor.
     * @param string $lichting Lichting om eetplan voor op te halen, 2 cijfers.
     */
    public function __construct($lichting)
    {
        parent::__construct();
        $this->lichting = $lichting;
    }

    public function getBekenden() {
        return $this->find('uid1 LIKE ?', array("$this->lichting%"))->fetchAll();
    }
}
