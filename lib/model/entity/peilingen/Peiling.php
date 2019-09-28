<?php

namespace CsrDelft\model\entity\peilingen;

use CsrDelft\model\peilingen\PeilingOptiesModel;
use CsrDelft\model\peilingen\PeilingStemmenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property PeilingOptie[] opties
 */
class Peiling extends PersistentEntity {

	public $id;
	public $titel;
	public $beschrijving;
	public $eigenaar;
	public $mag_bewerken;
	public $resultaat_zichtbaar;
	public $aantal_voorstellen;
	public $aantal_stemmen;
	public $rechten_stemmen;
	public $rechten_mod;
	public $sluitingsdatum;

	public function getOpties() {
		return PeilingOptiesModel::instance()->find('peiling_id = ?', [$this->id])->fetchAll();
	}

	public function getAantalGestemd() {
		return array_reduce($this->opties, function (int $carry, PeilingOptie $optie) {
			return $carry + $optie->stemmen;
		}, 0);
	}

	public function getMagBewerken() {
		//Elk BASFCie-lid heeft voorlopig peilingbeheerrechten.
		return LoginModel::mag(P_ADMIN . ',bestuur,commissie:BASFCie');
	}

	public function getIsMod() {
		return LoginModel::mag(P_PEILING_MOD) || LoginModel::getUid() == $this->eigenaar;
	}

	private function isPeilingOpen()
	{
		return $this->sluitingsdatum == NULL || time() < strtotime($this->sluitingsdatum);
	}

	public function getMagStemmen() {
		return LoginModel::mag(P_PEILING_VOTE) && ($this->eigenaar == LoginModel::getUid() || empty(trim($this->rechten_stemmen)) || LoginModel::mag($this->rechten_stemmen))
			&& $this->isPeilingOpen();
	}

	public function getHeeftGestemd() {
		return PeilingStemmenModel::instance()->heeftGestemd($this->id, LoginModel::getUid());
	}

	protected static $table_name = 'peiling';
	protected static $primary_key = ['id'];
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'titel' => [T::String],
		'beschrijving' => [T::Text],
		'eigenaar' => [T::UID],
		'mag_bewerken' => [T::Boolean],
		'resultaat_zichtbaar' => [T::Boolean],
		'aantal_voorstellen' => [T::Integer],
		'aantal_stemmen' => [T::Integer],
		'rechten_stemmen' => [T::String, true],
		'rechten_mod' => [T::String, true],
		'sluitingsdatum' => [T::DateTime, true],
	];

	protected static $computed_attributes = [
		'is_mod' => [T::Boolean],
		'heeft_gestemd' => [T::Boolean],
		'mag_stemmen' => [T::Boolean],
		'aantal_gestemd' => [T::Integer],
		'opties' => []
	];

	public function magBekijken()
	{
		return LoginModel::mag(P_LOGGED_IN);
	}

}


