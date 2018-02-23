<?php

namespace CsrDelft\model\fiscaat\pin;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchModel extends PersistenceModel {
	const ORM = PinTransactieMatch::class;

	/**
	 * @param PinTransactieMatch[] $matches
	 */
	public function createAll($matches) {
		foreach ($matches as $match) {
			$this->create($match);
		}
	}
}
