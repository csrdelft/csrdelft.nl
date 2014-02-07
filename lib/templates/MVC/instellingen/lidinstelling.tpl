{*
	lidinstelling.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<label style="float: left; width: 250px;" for="inst_{$module}_{$id}">
{if $iedereen}
	<img src="{$CSR_PICS}famfamfam/user_orange.png" title="Instellen voor alle leden" onclick="if(confirm('Weet u zeker dat u de instelling voor alle leden wilt veranderen?')){ location.href='/instellingen/reset/{$module}/{$id}/' + $('#inst_{$module}_{$id}').val(); };" style="cursor: pointer; border: 1px solid #999;" />&nbsp;
{/if}
	{$label}
</label>
{if $type === 'enum'}
	<select type="select" id="inst_{$module}_{$id}" name="{$module}_{$id}">
	{foreach from=$opties item=optie}
		<option value="{$optie}"{if $optie === $waarde} selected="selected"{/if}>{ucfirst($optie)}</option>
	{/foreach}
	</select>
{else}
	<input type="text" id="inst_{$module}_{$id}" name="{$module}_{$id}" value="{$waarde}"{if $type === 'string'} maxlength="{$opties[1]}"{/if} />
{/if}
&nbsp;({ucfirst($default)})
<br /><br />
{/strip}