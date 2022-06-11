<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCitaat extends BbTag
{
	public $bron_text = null;
	/** @var Profiel */
	public $bron_profiel = null;
	/** @var string */
	public $bron_url = null;
	private $hidden = false;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(ProfielRepository $profielRepository)
	{
		$this->profielRepository = $profielRepository;
	}

	public static function getTagName()
	{
		return 'citaat';
	}

	public function renderPlain()
	{
		$text = 'Citaat';
		if ($this->bron_profiel != null) {
			$text .= ' van ' . $this->bron_profiel->getNaam('user');
		} elseif ($this->bron_text != null) {
			if ($this->bron_url != null) {
				$text .= ' van ' . $this->bron_text . ' (' . $this->bron_url . ')';
			} else {
				$text .= ' van ' . $this->bron_url;
			}
		}
		return $text . ":\n " . trim($this->getContent());
	}

	public function renderLight()
	{
		$text = '<div class="citaatContainer bb-tag-citaat">Citaat';
		if ($this->bron_profiel != null) {
			$text .=
				' van ' .
				BbHelper::lightLinkInline(
					$this->env,
					'lid',
					'/profiel/' . $this->bron_profiel->uid,
					$this->bron_profiel->getNaam('user')
				);
		} elseif ($this->bron_text != null) {
			if ($this->bron_url != null) {
				$text .=
					' van ' .
					BbHelper::lightLinkInline(
						$this->env,
						'url',
						$this->bron_url,
						$this->bron_text
					);
			} else {
				$text .= ' van ' . $this->bron_url;
			}
		}
		return $text .
			':<div class="citaat">' .
			trim($this->getContent()) .
			'</div></div>';
	}

	/**
	 * Citaat
	 *
	 * @param optional String $arguments['citaat'] Naam of lidnummer van wie geciteerd wordt
	 * @param optional String $arguments['url'] Link naar bron van het citaat
	 *
	 * @return string
	 * @example [citaat=Jan_Lid url=https://csrdelft.nl]Citaat[/citaat]
	 * @example [citaat]Citaat[/citaat]
	 * @example [citaat=1234]Citaat[/citaat]
	 */
	public function render($arguments = [])
	{
		if (!$this->hidden) {
			$content = $this->getContent();
		} else {
			$content =
				'<div onclick="$(this).children(\'.citaatpuntjes\').slideUp();$(this).children(\'.meercitaat\').slideDown();"><div class="meercitaat verborgen">' .
				$this->getContent() .
				'</div><div class="citaatpuntjes" title="Toon citaat">...</div></div>';
		}
		$text = '<div class="citaatContainer bb-tag-citaat"><em>Citaat';

		if ($this->bron_profiel != null) {
			$text .= ' van ' . $this->bron_profiel->getLink('user');
		} elseif ($this->bron_text != null) {
			if ($this->bron_url != null) {
				$text .= ' van ' . external_url($this->bron_url, $this->bron_text);
			} else {
				$text .= ' van ' . $this->bron_text;
			}
		}

		return $text .
			':</em><blockquote>' .
			trim($content) .
			'</blockquote></div>';
	}

	public function parse($arguments = [])
	{
		$this->env->quote_level++;
		$this->readContent();
		$this->env->quote_level--;
		$this->hidden = $this->env->quote_level > 1;
		if (isset($arguments['citaat'])) {
			$bron = $arguments['citaat'];
			$profiel = LoginService::mag('ROLE_LEDEN_READ,ROLE_OUDLEDEN_READ')
				? $this->profielRepository->find($bron)
				: null;
			if ($profiel) {
				$this->bron_profiel = $profiel;
			} else {
				$this->bron_text = str_replace('_', ' ', $bron);
			}
		}
		if (isset($arguments['url']) && url_like($arguments['url'])) {
			$this->bron_url = $arguments['url'];
		}
	}
}
