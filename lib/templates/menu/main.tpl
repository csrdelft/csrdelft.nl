<nav class="cd-nav">
	<ul id="cd-primary-nav" class="cd-primary-nav is-fixed"{if (CsrDelft\model\LidInstellingenModel::get('layout', 'fx') != 'nee')} style="opacity:0.8;"{/if}>
		{if CsrDelft\model\security\LoginModel::mag('P_LOGGED_IN')}
			<li class="has-children">
				<a id="cd-main-trigger" href="#0">
					<img id="cd-user-avatar" class="cd-user-avatar" src="/plaetjes/{CsrDelft\model\security\LoginModel::getProfiel()->getPasfotoPath(true)}">
					{CsrDelft\model\security\LoginModel::getProfiel()->getNaam('civitas')}
					{if $gesprekOngelezen > 0}&nbsp;<span class="badge badge-red" title="{$gesprekOngelezen} ongelezen bericht{if $gesprekOngelezen !== 1}en{/if}">{$gesprekOngelezen}</span>{/if}
				</a>
				<ul class="cd-secondary-nav is-hidden">
					{include file='menu/main_tree.tpl' parent=$root}
				</ul>
			</li>
		{else}
			<li><a href="/">Log in</a></li>
			{/if}
	</ul>
</nav>
<div id="cd-search" class="cd-search">
	{$zoekbalk->view()}
</div>