<h2>
	<a href="/forum/" class="forumGrootlink">Forum</a> &raquo;
	<a href="/forum/categorie/{$topic->getCategorieID()}" class="forumGrootlink">{$topic->getCategorieTitel()}</a> &raquo; 
	{$topic->getTitel()}
</h2>
{* topic mod dingen: *}
{if $topic->magModereren()}
	U mag dit topic modereren:<br />
		<a href="/forum/verwijder-onderwerp/{$topicID}" onclick="return confirm('Weet u zeker dat u dit topic wilt verwijderen?')">verwijderen</a>
	{if $topic->isOpen()}
		| <a href="/forum/sluit-onderwerp/{$topicID}">sluiten (reageren niet meer mogelijk)</a>
	{else}	
		| <a href="/forum/open-onderwerp/{$topicID}">weer openen (reageren weer w&eacute;l mogelijk)</a> 
	{/if}
	{if $topic->isPlakkerig()}
		| <a href="/forum/maak-plakkerig/{$topicID}'">maak plakkerig</a>
	{else}
		| <a href="/forum/maak-niet-plakkerig/{$topicID}">verwijder plakkerigheid</a> 
	{/if}
	<br /><br />
{/if}
<table class="forumtabel">
	<tr>
		<td class="forumhoofd">auteur</td>
		<td class="forumhoofd">bericht</td>
	</tr>
	{* speciale topic weergeven als het topic er een is. bijvoorbeeld een poll *}
	{if $topic->getSoort()=='T_POLL'}
		{* $pollContent *}
	{/if}	
	{php}
		while($aPost=$this->_tpl_vars['topic']->nextPost()){
		$this->_tpl_vars['aPost']=$aPost;
	{/php}
	<tr>
		<td class="forumauteur">
			<a href="/leden/profiel/{$aPost.uid}">
				{$aPost.naam}
			</a> schreef 
			<a class="forumpostlink" name="{$aPost.postID}">{$aPost.datum}</a>
			{if $aPost.bewerkDatum!=''}
				bewerk op: {$aPost.bewerkDatum}
			{/if}
			<br />
			{if $topic->magCiteren()}
				<a href="/forum/reactie/{$aPost.postID}">
					<img src="/images/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" />
				</a>
			{/if}
			{if $topic->magBewerken()}
				<a href="/forum/bewerken/{$aPost.postID}">
					<img src="/images/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht " style="border: 0px;" />
				</a>
			{/if}
			{if $topic->magVerwijderen()}
				<a href="/forum/verwijder-bericht/{$aPost.postID}" onclick="return confirm('Weet u zeker dat u deze post wilt verwijderen?')">
					<img src="/images/verwijderen.png" title="Verwijder bericht" alt=" " style="border: 0px;" />
				</a>
			{/if}
		</td>
		{* het bericht: *}
		<td class="forumbericht{cycle values="0,1"}">
			{$aPost.bericht}	
		</td>
	</tr>
	<tr>
		<td class="forumtussenschot" colspan="2"></td>
	</tr>
	{php} } {/php}
	<tr>
		<td class="forumauteur">
			<a class="forumpostlink" name="laatste">Snel reageren:</a>
			{if $topic->magPosten() AND $topic->isOpen()}
				<br /><strong>Dit topic is gesloten, u mag reageren omdat u beheerder bent.</strong>
			{/if}
		</td>
		<td class="forumtekst">
			{if $topic->magPosten()}
				<form method="post" action="/forum/toevoegen/{$topicID}">
					<p>
						<textarea name="bericht" class="tekst" rows="6" cols="80" style="width: 100%;" ></textarea><br />
						<input type="submit" name="submit" value="opslaan" />
					</p>
				</form>
			{else}
				{if $topic->isOpen()}
					U mag hier niet reageren omdat u niet bent ingelogged.
				{else}
					U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
				{/if}
			{/if}
		</td>
	</tr>
</table>
