<?php

namespace CsrDelft\view\formulier\uploadvelden\required;

use CsrDelft\view\formulier\uploadvelden\FileField;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * @see FileField
 */
class RequiredFileField extends FileField
{
	public $required = true;
}
