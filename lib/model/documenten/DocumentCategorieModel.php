<?php

namespace CsrDelft\model\documenten;
use CsrDelft\model\entity\documenten\DocumentCategorie;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class DocumentCategorieModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentCategorieModel extends PersistenceModel  {
	const ORM = DocumentCategorie::class;

	protected static $instance;
}
