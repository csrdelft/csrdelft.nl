<?php
/**
 * modifier.bedrag.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

function smarty_modifier_bedrag($bedrag) {
	$bedragFloat = $bedrag / 100;
	return sprintf('€%.2f', $bedragFloat);

}
