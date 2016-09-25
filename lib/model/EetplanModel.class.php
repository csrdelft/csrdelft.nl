<?php

require_once 'model/entity/Eetplan.class.php';
require_once 'model/entity/EetplanBekenden.class.php';
require_once 'model/EetplanFactory.class.php';

/**
 * EetplanModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Verzorgt het opvragen van eetplangegevens
 */
class EetplanModel extends PersistenceModel {
    protected static $instance;

    const ORM = 'Eetplan';

    public function getEetplanVoorAvond($avond) {
        return $this->find('avond = ?', array($avond));
    }

    public function getNovieten($lichting) {
        return $this->findSparse(array('uid'), 'uid LIKE ?', array(sprintf("%s%%", $lichting)), 'uid');
    }

    /**
     * Haal alle avonden op die voor deze lichting gelden.
     *
     * @return Eetplan[] Lijst met sparse(!) eetplan objecten met alleen een avond.
     */
    public function getAvonden($lichting) {
        return $this->findSparse(array('avond'), 'uid LIKE ?', array(sprintf("%s%%", $lichting)), 'avond')->fetchAll();
    }

    /**
     * Haal het volledige eetplan op (voor de huidige lichting)
     *
     * Uitvoer is een array met 'uid' => [Eetplan, Eetplan, ...]
     *
     * @return array Het eetplan
     */
    public function getEetplan($lichting) {
        // Avond 0000-00-00 wordt gebruikt voor novieten die huizen kennen
        // Orderen bij avond, zodat de avondvolgorde per noviet klopt
        $eetplan = $this->find('uid LIKE ? AND avond <> "0000-00-00"', array(sprintf("%s%%", $lichting)), null, 'avond');
        $eetplanFeut = array();
        $avonden = array();
        foreach ($eetplan as $sessie) {
            if (!isset($eetplanFeut[$sessie->uid])) {
                $eetplanFeut[$sessie->uid] = array(
                    'avonden' => array(),
                    'uid' => $sessie->uid,
                    'naam' => $sessie->getNoviet()->getNaam()
                );
            }

            $eetplanFeut[$sessie->uid]['avonden'][] = array(
                'datum' => $sessie->avond,
                'woonoord_id' => $sessie->woonoord_id,
                'woonoord' => $sessie->getWoonoord()->naam
            );

            if (!isset($avonden[$sessie->avond])) {
                $avonden[$sessie->avond] = $sessie->avond;
            }
        }

        return array(
            'novieten' => array_values($eetplanFeut),
            'avonden' => array_values($avonden)
        );
    }

    public function maakEetplan($avond, $lichting) {
        // Laad oude dingen in
        // Laad sjaars die elkaar kennen in

        $factory = new EetplanFactory();

        $bekenden = EetplanBekendenModel::instance()->getBekenden($lichting);
        $factory->setBekenden($bekenden);

        $bezocht = $this->find("uid LIKE ?", array(sprintf("%s%%", $lichting)));
        $factory->setBezocht($bezocht);

        $novieten = ProfielModel::instance()->find("uid LIKE ? AND status = 'S_NOVIET'", array(sprintf("%s%%", $lichting)))->fetchAll();
        $factory->setNovieten($novieten);

        $huizen = WoonoordenModel::instance()->find("eetplan = true")->fetchAll();
        $factory->setHuizen($huizen);

        return $factory->genereer($avond, true);
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
    public function getEetplanVoorHuis($id, $lichting) {
        return $this->find('uid LIKE ? AND woonoord_id = ?', array(sprintf("%s%%", $lichting), $id), null, 'avond')->fetchAll();
    }

    public function getBekendeHuizen($lichting) {
        return $this->find('uid LIKE ? AND avond = DATE(0)', array(sprintf("%s%%", $lichting)))->fetchAll();
    }
}

class EetplanBekendenModel extends PersistenceModel {
    protected static $instance;

    const ORM = "EetplanBekenden";

    /**
     * EetplanBekenden constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    public function getBekenden($lichting) {
        return $this->find('uid1 LIKE ?', array(sprintf("%s%%", $lichting)))->fetchAll();
    }

    public function exists(PersistentEntity $entity) {
        if (parent::exists($entity)) {
            return true;
        }

        $omgekeerd = new EetplanBekenden();
        $omgekeerd->uid1 = $entity->uid2;
        $omgekeerd->uid2 = $entity->uid1;

        return parent::exists($omgekeerd);
    }
}
