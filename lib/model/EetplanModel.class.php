<?php
namespace CsrDelft\model;

use CsrDelft\model\entity\Eetplan;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\Orm\PersistenceModel;


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

    const ORM = Eetplan::class;

    public function getEetplanVoorAvond($avond) {
        return $this->find('avond = ?', array($avond));
    }

    public function getNovieten($lichting) {
        return $this->find('uid LIKE ?', array($lichting . "%"), 'uid');
    }

	/**
	 * Haal alle avonden op die voor deze lichting gelden.
	 *
	 * @param $lichting
	 * @return Eetplan[] Lijst met eetplan objecten met alleen een avond.
	 */
    public function getAvonden($lichting) {
        return $this->find('uid LIKE ?', array($lichting . "%"), 'avond')->fetchAll();
    }

	/**
	 * Haal het volledige eetplan op (voor de huidige lichting)
	 *
	 * Uitvoer is een array met 'uid' => [Eetplan, Eetplan, ...]
	 *
	 * @param $lichting
	 * @return array Het eetplan
	 */
    public function getEetplan($lichting) {
        // Avond 0000-00-00 wordt gebruikt voor novieten die huizen kennen
        // Orderen bij avond, zodat de avondvolgorde per noviet klopt
		/** @var Eetplan[] $eetplan */
        $eetplan = $this->find('uid LIKE ? AND avond <> "0000-00-00"', array($lichting . "%"), null, 'avond');
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

        /** @var Eetplan[] $bezocht */
        $bezocht = $this->find("uid LIKE ?", array($lichting . "%"));
        $factory->setBezocht($bezocht);

        $novieten = ProfielModel::instance()->find("uid LIKE ? AND status = 'S_NOVIET'", array($lichting . "%"))->fetchAll();
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
        return $this->find('uid = ? AND avond <> "0000-00-00"', array($uid), null, 'avond')->fetchAll();
    }

	/**
	 * @param $id int Id van het huis
	 * @param $lichting
	 * @return Eetplan[] lijst van eetplansessies voor dit huis, gesorteerd op datum (oplopend)
	 */
    public function getEetplanVoorHuis($id, $lichting) {
        return $this->find('uid LIKE ? AND woonoord_id = ? AND avond <> "0000-00-00"', array($lichting . "%", $id), null, 'avond')->fetchAll();
    }

    public function getBekendeHuizen($lichting) {
        return $this->find('uid LIKE ? AND avond = DATE(0)', array($lichting . "%"))->fetchAll();
    }
}
