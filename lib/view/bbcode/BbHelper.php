<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\bb\BbEnv;
use CsrDelft\common\Util\HostUtil;

/**
 * Een paar helper functies voor bb.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/07/2019
 */
final class BbHelper
{
	/**
	 * Templates for light mode
	 * @param BbEnv $env
	 * @param string $tag
	 * @param string $url
	 * @param string $content
	 * @return string
	 */
	public static function lightLinkInline($env, $tag, $url, $content): string
	{
		if (isset($url[0]) && $url[0] === '/') {
			// Zorg voor werkende link in e-mail
			$url = HostUtil::getCsrRoot() . $url;
		}

		return <<<HTML
			<a class="bb-link-inline bb-tag-{$tag}" href="{$url}">{$content}</a>
HTML;
	}

	/**
	 * @param string $tag
	 * @param string $url
	 * @param string $titel
	 * @param string $beschrijving
	 * @param string $thumbnail
	 * @return string
	 */
	public static function lightLinkBlock(
		$tag,
		$url,
		$titel,
		$beschrijving,
		$thumbnail = ''
	): string {
		$titel = htmlspecialchars($titel);
		$beschrijving = htmlspecialchars($beschrijving);
		if ($thumbnail !== '') {
			$thumbnail = '<img src="' . $thumbnail . '" />';
		}
		return <<<HTML
			<a class="bb-link-block bb-tag-{$tag}" href="{$url}">
				{$thumbnail}
				<h2>{$titel}</h2>
				<p>{$beschrijving}</p>
			</a>
HTML;
	}

	/**
	 * @param string $tag
	 * @param string $url
	 * @param string $thumbnail
	 * @return string
	 */
	public static function lightLinkThumbnail($tag, $url, $thumbnail): string
	{
		return <<<HTML
			<a class="bb-link-thumbnail bb-tag-{$tag}" href="{$url}">
				<img src="{$thumbnail}" />
			</a>
HTML;
	}
}
