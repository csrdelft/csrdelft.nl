<nav id="cd-lateral-nav">
	<ul class="cd-navigation cd-single-item-wrapper">
		<li>
			<form id="menuZoekForm" name="lidzoeker" method="get" action="/communicatie/lijst.php">
				<input id="menuZoekveld" name="q" type="text" />
				<script type="text/javascript">
					$(document).ready(function () {
						$('#menuZoekveld').autocomplete({json_encode(array_keys($instantsearch))}, {
							clickFire: true,
							max: 20,
							matchContains: true,
							noRecord: ""
						});
						var instantsearch = {json_encode($instantsearch)};
						$('#menuZoekveld').click(function (event) {
							this.setSelectionRange(0, this.value.length);
						});
						$('#menuZoekveld').keyup(function (event) {
							if (event.keyCode === 13 && typeof instantsearch[this.value] !== 'undefined') { // enter
								window.location.href = instantsearch[this.value]; // goto url
							}
						});
					});
				</script>
			</form>
		</li>
	</ul>
	<ul class="cd-navigation">
		{foreach from=$root->children item=item}
			{if $item->zichtbaar and $item->magBekijken()}
				<li class="{if $item->hasChildren()}item-has-children{/if} {if $item->active}active{/if}">
					<a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a>
					{if $item->hasChildren()}
						<ul class="sub-menu">
							{foreach from=$item->children item=child}
								{if $child->zichtbaar and $child->magBekijken()}
									<li><a href="{$child->link}" title="{$child->tekst}"{if $child->active} class="active"{/if}>{$child->tekst}</a></li>
								{/if}
								{foreach from=$child->children item=level3}
									{if $level3->zichtbaar and $level3->magBekijken()}
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