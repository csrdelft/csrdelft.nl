<?php


namespace CsrDelft\model;


use CsrDelft\model\entity\courant\CourantBericht;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;

class CourantBerichtModel extends PersistenceModel {
	const ORM = CourantBericht::class;

	protected $default_order = 'volgorde ASC';

	/**
	 * @return CourantBericht[]
	 */
	public function getBerichten() {
		return $this->find()->fetchAll();
	}

	/**
	 * @param $id
	 * @return CourantBericht|false
	 */
	public function get($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}

	public function getBerichtenVoorGebruiker() {
		//mods en bestuur zien alle berichten
		if ($this->magBeheren() || LoginModel::mag('bestuur')) {
			return $this->getBerichten();
		} else {
			return $this->find('uid = ?', [LoginModel::getUid()])->fetchAll();
		}
	}

	public function magBeheren($uid = null) {
		return LoginModel::mag(P_MAIL_COMPOSE) || LoginModel::mag($uid);
	}
}
