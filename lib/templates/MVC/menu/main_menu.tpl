<nav id="cd-lateral-nav">
	<ul class="cd-navigation cd-single-item-wrapper">
		<li>
			<form id="cd-zoek-form" action="/communicatie/lijst.php">
				<div class="input-group">
					<input type="text" id="cd-zoek-veld" name="q" class="form-control">
					<div class="input-group-btn">
						<button id="cd-zoek-engines" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><img src="http://plaetjes.csrdelft.nl/knopjes/search-16.png"> <span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right" role="menu">
							<li><a class="submit">Leden & Groepen</a></li>
							<li class="divider"></li>
							<li><a href="/forum/zoeken/" class="submit" onclick="this.href += encodeURIComponent($('#cd-zoek-veld').val());">Forum</a></li>
							<li><a href="/wiki/hoofdpagina?do=search&id=" class="submit" onclick="this.href += encodeURIComponent($('#cd-zoek-veld').val());">Wiki</a></li>
						</ul>
					</div><!-- /btn-group -->
				</div><!-- /input-group -->
				<script type="text/javascript">
					$(document).ready(function () {
						try {
							var instantsearch = {json_encode($mainmenu->getInstantSearchSuggestions())};
							$('#cd-zoek-veld').typeahead({
								autoselect: true,
								hint: true,
								highlight: true,
								minLength: 1
							}, {
								name: "instantsearch",
								displayKey: "key",
								source: substringMatcher(instantsearch, true)
							});
							$('#cd-zoek-veld').click(function (event) {
								this.setSelectionRange(0, this.value.length);
							});
						}
						catch (err) {
							// Missing js file
						}
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
								else {
									this.form.submit();
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
					<a class="{if $item->hasChildren()}toggle-group{/if}" href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a>
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