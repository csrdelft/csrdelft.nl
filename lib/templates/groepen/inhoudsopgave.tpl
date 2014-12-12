<div id="groepLijst">
	<ul>
		{foreach from=$groepen item=groep}
			<li>
				<a href="#groep{$groep->id}">
					{$groep->naam}
				</a>
			</li>
		{/foreach}	
	</ul>
</div>