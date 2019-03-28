<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\ProfielModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCitaat extends BbTag {
	public function parseLight($arguments = []) {
		if ($this->env->quote_level == 0) {
			$this->env->quote_level = 1;
			$content = $this->getContent();
			$this->env->quote_level = 0;
		} else {
			$this->getContent();
			$content = "...";
		}

		$text = '<div class="citaatContainer bb-tag-citaat">Citaat';
		$van = '';
		if (isset($arguments['citaat'])) {
			$van = trim(str_replace('_', ' ', $arguments['citaat']));
		}
		$profiel = ProfielModel::get($van);
		if ($profiel) {
			$text .= ' van ' . $this->lightLinkInline('lid', '/profiel/' . $profiel->uid, $profiel->getNaam('user'));
		} elseif ($van != '') {
			if (isset($arguments['url']) && url_like($arguments['url'])) {
				$text .= ' van ' . $this->lightLinkInline('url', $arguments['url'], $van);
			} else {
				$text .= ' van ' . $van;
			}
		}
		return $text . ':<div class="citaat">' . trim($content) . '</div></div>';
	}

	/**
	 * Citaat
	 *
	 * @param optional String $arguments['citaat'] Naam of lidnummer van wie geciteerd wordt
	 * @param optional String $arguments['url'] Link naar bron van het citaat
	 *
	 * @example [citaat=1234]Citaat[/citaat]
	 * @example [citaat=Jan_Lid url=https://csrdelft.nl]Citaat[/citaat]
	 * @example [citaat]Citaat[/citaat]
	 */
	public function parse($arguments = array()) {
		if ($this->env->quote_level == 0) {
			$this->env->quote_level = 1;
			$content = $this->getContent();
			$this->env->quote_level = 0;
		} else {
			$this->env->quote_level++;
			$content = $this->getContent();
			$this->env->quote_level--;
			$content = '<div onclick="$(this).children(\'.citaatpuntjes\').slideUp();$(this).children(\'.meercitaat\').slideDown();"><div class="meercitaat verborgen">' . $content . '</div><div class="citaatpuntjes" title="Toon citaat">...</div></div>';
		}
		$text = '<div class="citaatContainer bb-tag-citaat">Citaat';
		$van = '';
		if (isset($arguments['citaat'])) {
			$van = trim(str_replace('_', ' ', $arguments['citaat']));
		}
		$profiel = ProfielModel::get($van);
		if ($profiel) {
			$text .= ' van ' . $profiel->getLink('user');
		} elseif ($van != '') {
			if (isset($arguments['url']) && url_like($arguments['url'])) {
				$text .= ' van ' . external_url($arguments['url'], $van);
			} else {
				$text .= ' van ' . $van;
			}
		}
		return $text . ':<div class="citaat">' . trim($content) . '</div></div>';
	}

	public function getTagName() {
		return 'citaat';
	}
}
