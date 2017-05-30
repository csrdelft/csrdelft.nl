<?php

namespace CsrDelft\model\documenten;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class DocumentModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentModel extends PersistenceModel {
	const ORM = \CsrDelft\model\entity\documenten\Document::class;

	protected static $instance;
}
