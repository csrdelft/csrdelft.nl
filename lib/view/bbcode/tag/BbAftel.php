<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

class BbAftel extends BbTag {

	public static function getTagName() {
		return ['aftel'];
	}

	public function render() {
		if (!isset($_ENV['AFTEL_START'])
			|| $_ENV['AFTEL_START'] > time()
			|| $_ENV['AFTEL_STOP'] < time()) {
			return "";
		}

		$id = uniqid_safe("flipdown_");
		return <<<HTML
<link rel="stylesheet" href="https://unpkg.com/flipdown@0.3.2/dist/flipdown.min.css">
<script src="https://unpkg.com/flipdown@0.3.2/dist/flipdown.min.js"></script>
<div class="aftel bb-block">
	<div id="{$id}" class="flipdown"></div>
	<a href="/aftel" class="btn btn-primary" style="display: none;margin-top: 20px;" id="end-button" target="_blank">Het moment &rarr;</a>
</div>
<script>
	new FlipDown({$_ENV['AFTEL_EIND']}, '{$id}', {
		headings: ["Dagen", "Uren", "Minuten", "Seconden"],
	})
		.start()
		.ifEnded(function() {
			$('#end-button').slideDown();
		});
</script>
<style>
	.aftel {
		text-align: center;
	}

	.aftel .flipdown {
		margin: 0 auto;
	}
</style>
HTML;
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []) {
	}
}
