<?php

/**
 * Class Peiling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class Peiling extends PersistentEntity {
    public $id;
    public $titel;
    public $tekst;

    private $opties;

    public function getStemmenAantal() {
        return PeilingStemmenModel::instance()->count('peilingid = ?', array($this->id));
    }

    public function getOpties() {
        if ($this->opties == null) {
            $this->opties = PeilingOptiesModel::instance()->find('peilingid = ?', array($this->id))->fetchAll();
        }
        return $this->opties;
    }

    public function nieuwOptie($optie) {
        $this->opties[] = $optie;
    }

    public static function magBewerken() {
		//Elk BASFCie-lid heeft voorlopig peilingbeheerrechten.
		return LoginModel::mag('P_ADMIN,bestuur,commissie:BASFCie');
	}

    public function magStemmen() {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		return $this->hasVoted() == '';
	}

    public function hasVoted() {
        return PeilingStemmenModel::instance()->exist('peilingid = ? AND uid = ?', array($this->id, LoginModel::getUid()));
    }

    protected static $table_name = 'peiling';
    protected static $primary_key = array('id');
    protected static $persistent_attributes = array(
        'id'    => array(T::UnsignedInteger, false, 'auto_increment'),
        'titel' => array(T::String),
        'tekst' => array(T::Text)
    );
}


