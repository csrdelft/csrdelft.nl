{strip}
	<div class="item">
		{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
		{if date('d-m', $timestamp) === date('d-m')}
			{$timestamp|date_format:"%H:%M"}
		{elseif strftime('%U', $timestamp) === strftime('%U')}
			<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
		{else}
			{$timestamp|date_format:"%d-%m"}
		{/if}
		&nbsp;
		<a href="/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}" title="[{$draad->titel}] {$draad->laatste_lid_id|csrnaam:'user':false:false|escape:'html'}: {$posts[0]->tekst|truncate:25:"…":true}"
		   {if !$draad->alGelezen()} class="opvallend"{/if}>{$draad->titel|truncate:25:"…":true}
		</a>
		<br />
	</div>
{/strip}