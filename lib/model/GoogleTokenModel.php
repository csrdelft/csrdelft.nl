<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\GoogleToken;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class GoogleTokenModel.
 *
 * @author G.J.W. Oolbekkink Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class GoogleTokenModel extends PersistenceModel {
	const ORM = GoogleToken::class;
}
