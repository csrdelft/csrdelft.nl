<nav id="menu" class="cd-nav">
	<ul id="cd-primary-nav" class="cd-primary-nav"{if (CsrDelft\model\LidInstellingenModel::get('layout', 'fx') != 'nee')} style="opacity:0.8;"{/if}>
		{toegang P_LOGGED_IN}
			<li>
				<a id="cd-main-trigger" class="mobiel-hidden trigger" href="#menu">
					<img id="cd-user-avatar" class="cd-user-avatar" src="/plaetjes/pasfoto/{CsrDelft\model\security\LoginModel::getProfiel()->getPasfotoPath(true)}">
					{CsrDelft\model\security\LoginModel::getProfiel()->getNaam('civitas')}
					{if $gesprekOngelezen > 0}&nbsp;<span class="badge badge-red" title="{$gesprekOngelezen} ongelezen bericht{if $gesprekOngelezen !== 1}en{/if}">{$gesprekOngelezen}</span>{/if}
				</a>
				<ul class="cd-secondary-nav">
					{include file='menu/main_tree.tpl' parent=$root}
				</ul>
			</li>
			<li class="mobiel-hidden"><a class="trigger" href="#search"><i class="fa fa-search"></i></a></li>
		{geentoegang}
			<li><a href="/">Log in</a></li>
		{/toegang}
	</ul>
</nav>
<div id="search" class="cd-search">
	{$zoekbalk->view()}
</div>
