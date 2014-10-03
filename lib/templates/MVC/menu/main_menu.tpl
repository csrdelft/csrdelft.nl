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
			<li class="{if $item->hasChildren()}item-has-children{/if} {if $item->current}current{/if}">
				<a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a>
				{if $item->hasChildren()}
					<ul class="sub-menu">
						{foreach from=$item->children item=child}
							<li><a href="{$child->link}" title="{$child->tekst}"{if $child->current} class="current"{/if}>{$child->tekst}</a></li>
						{/foreach}
					</ul>
				{/if}
			</li>
		{/foreach}
	</ul>
</nav>