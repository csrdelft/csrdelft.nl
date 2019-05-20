<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\common\CsrException;
use CsrDelft\model\LidInstellingenModel;

/**
 * Toont content als instelling een bepaalde waarde heeft, standaard 'ja';
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [instelling=maaltijdblokje module=voorpagina][maaltijd=next][/instelling]
 */
class BbInstelling extends BbTag {

	public function getTagName() {
		return 'instelling';
	}

	public function parse($arguments = []) {
		$content = $this->getContent();
		if (!array_key_exists('instelling', $arguments) || !isset($arguments['instelling'])) {
			return 'Geen of een niet bestaande instelling opgegeven: ' . htmlspecialchars($arguments['instelling']);
		}
		if (!array_key_exists('module', $arguments) || !isset($arguments['module'])) { // backwards compatibility
			$key = explode('_', $arguments['instelling'], 2);
			$arguments['module'] = $key[0];
			$arguments['instelling'] = $key[1];
		}
		$testwaarde = 'ja';
		if (isset($arguments['waarde'])) {
			$testwaarde = $arguments['waarde'];
		}
		try {
			if (LidInstellingenModel::get($arguments['module'], $arguments['instelling']) == $testwaarde) {
				return $content;
			}
		} catch (CsrException $e) {
			return '[instelling]: ' . $e->getMessage();
		}

		return '';
	}
}
