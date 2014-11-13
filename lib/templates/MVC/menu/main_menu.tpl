<nav id="cd-lateral-nav">
	<ul class="cd-navigation cd-single-item-wrapper">
		<li>
			<form id="cd-zoek-form" name="lidzoeker" method="get" action="/communicatie/lijst.php">
				<input id="cd-zoek-veld" name="q" type="text" />
				<script type="text/javascript">
					$(document).ready(function () {
					{assign var=instantsearch value=$mainmenu->getInstantSearchSuggestions()}
						$('#cd-zoek-veld').autocomplete({json_encode(array_keys($instantsearch))}, {
							clickFire: true,
							max: 20,
							matchContains: true,
							noRecord: ""
						});
						var instantsearch = {json_encode($instantsearch)};
						$('#cd-zoek-veld').click(function (event) {
							this.setSelectionRange(0, this.value.length);
						});
						$('#cd-zoek-veld').keyup(function (event) {
							if (event.keyCode === 13) { // enter
								if (typeof instantsearch[this.value] !== 'undefined') { // known shortcut
									window.location.href = instantsearch[this.value]; // goto url
								}
								else if (this.value.indexOf('su ') == 0) {
									window.location.href = '/su/' + this.value.substring(3);
								}
								else if (this.value == 'endsu') {
									window.location = '/endsu';
								}
							}
						});
					});
				</script>
			</form>
		</li>
	</ul>
	<ul class="cd-navigation">
		{foreach from=$root->children item=item}
			{if $item->magBekijken()}
				<li class="{if $item->hasChildren()}item-has-children{/if} {if $item->active}active{/if}">
					<a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a>
					{if $item->hasChildren()}
						<ul class="sub-menu">
							{foreach from=$item->children item=child}
								{if $child->magBekijken()}
									<li><a href="{$child->link}" title="{$child->tekst}"{if $child->active} class="active"{/if}>{$child->tekst}</a></li>
									{/if}
									{foreach from=$child->children item=level3}
										{if $level3->magBekijken()}
										<li class="verborgen"><a href="{$level3->link}" title="{$level3->tekst}"{if $level3->active} class="active"{/if}>{$level3->tekst}</a></li>
										{/if}
									{/foreach}
								{/foreach}
						</ul>
					{/if}
				</li>
			{/if}
		{/foreach}
	</ul>
</nav>