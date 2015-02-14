<nav class="cd-nav">
	<ul id="cd-primary-nav" class="cd-primary-nav is-fixed"{if (LidInstellingen::get('layout', 'fx') != 'nee')} style="opacity:0.8;"{/if}>
		{if LoginModel::mag('P_LOGGED_IN')}
			<li class="has-children">
				<a id="cd-main-trigger" href="#0">
					<img id="cd-user-avatar" src="/plaetjes/{LoginModel::getProfiel()->getPasfotoPath(true)}">
					{LoginModel::getProfiel()->getNaam('civitas')}
					{if $gesprekOngelezen > 0}<span class="badge badge-red" title="{$gesprekOngelezen} ongelezen berichten">{$gesprekOngelezen}</span>{/if}
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