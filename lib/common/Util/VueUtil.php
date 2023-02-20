<?php

namespace CsrDelft\common\Util;

final class VueUtil
{
	/**
	 * Zie register-vue.ts voor de lijst beschikbare namen.
	 *
	 * @param string $naam
	 * @param array $props
	 * @return string
	 */
	public static function vueComponent(string $naam, array $props): string
	{
		return vsprintf(
			'<span class="vue-component" data-naam="%s" data-props="%s"></span>',
			[$naam, TextUtil::vue_encode($props)]
		);
	}
}
