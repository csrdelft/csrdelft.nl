{*
	lidinstelling.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
	<label style="float: left; width: 250px;" for="inst_{$module}_{$id}">
		{if $reset}
			<img src="{$CSR_PICS}/famfamfam/arrow_rotate_anticlockwise.png" title="U gaat nu deze instelling voor iedereen resetten naar de standaard waarde. (Die moet in de code worden aangepast.)" onclick="if (confirm(this.title + '\n\nWeet u het zeker?')) {
				location.href = '/instellingen/reset/{$module}/{$id}/' + $('#inst_{$module}_{$id}').val();
			}" style="cursor: pointer; border: 1px solid #999;" />&nbsp;&nbsp;
		{/if}
		{$label}
	</label>
	{if $type === T::Enumeration}
		<select type="select" id="inst_{$module}_{$id}" name="{$module}_{$id}">
			{foreach from=$opties item=optie}
				<option value="{$optie}"{if $optie === $waarde} selected="selected"{/if}>{ucfirst($optie)}</option>
			{/foreach}
		</select>
	{else}
		<input type="text" id="inst_{$module}_{$id}" name="{$module}_{$id}" value="{$waarde}"{if $type === T::String} maxlength="{$opties[1]}" style="width: 111px;"{elseif $type === T::Integer} style="width: 33px;"{/if} />
	{/if}
	&nbsp;({ucfirst($default)})
	<br /><br />
{/strip}