<?php

namespace CsrDelft\model\entity\security;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/03/2019
 */
class Permission {
	public static function concat(...$permissions) {
		return join(',', $permissions);
	}

}
