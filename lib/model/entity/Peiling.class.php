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

    public function getStemmenAantal() {
        return PeilingStemmenModel::instance()->count('peilingid = ?', array($this->id));
    }

    public function getOpties() {
        return PeilingOptiesModel::instance()->find('peilingid = ?', array($this->id));
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

    public function stem() {
        $optie = PeilingOptiesModel::instance()->find('peilingid = ?', array($this->id))->fetch();
        $optie->stemmen += 1;
        PeilingOptiesModel::instance()->update($optie);
    }

    public function hasVoted() {
        return PeilingStemmenModel::instance()->exist('peilingid = ? AND uid = ?', array($this->id, LoginModel::getUid()));
    }

    protected static $table_name = 'peiling';
    protected static $primary_key = array('id');
    protected static $persistent_attributes = array(
        'id'    => array(T::Integer, false, 'auto_increment'),
        'titel' => array(T::String),
        'tekst' => array(T::Text)
    );
}


