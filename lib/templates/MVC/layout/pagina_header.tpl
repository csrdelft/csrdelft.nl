<header>{strip}
	<nav id="cd-top-nav">
		<ul>
			{if LoginModel::mag('P_LOGGED_IN')}
				<li id="cd-ingelogd-menu-trigger">
					<span class="cd-ingelogd-menu-text">{LoginModel::instance()->getLid()->getNaamLink('civitas', 'plain')}</span>
					<ul id="cd-ingelogd-menu">
						{if LoginModel::instance()->isSued()}
							<li><a href="/endsu/" class="error">SU {LoginModel::instance()->getSuedFrom()->getNaamLink('civitas', 'plain')}</a></li>
						{/if}
						<li><a href="/communicatie/profiel/{LoginModel::getUid()}#SocCieSaldo">Profiel</a></li>
						<li>
							<a href="/communicatie/profiel/{LoginModel::getUid()}">
                                {assign var=saldo value=LoginModel::instance()->getLid()->getSoccieSaldo()}
								SocCie: <span{if $saldo < 0} class="staatrood"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
							</a>
						</li>
						<li>
							<a href="/communicatie/profiel/{LoginModel::getUid()}#MaalCieSaldo">
								{assign var=saldo value=LoginModel::instance()->getLid()->getMaalcieSaldo()}
								MaalCie: <span{if $saldo < 0} class="error"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
							</a>
						</li>
						<li><a href="/instellingen">Instellingen</a></li>
						{if LidInstellingen::get('zijbalk', 'favorieten') == 'ja'}
							<li><a href="/menubeheer/toevoegen/favoriet" class="post modal addfav">Toevoegen aan favorieten</a></li>
						{/if}
						{if LoginModel::mag('P_LEDEN_MOD')}
							<li><a href="/forum/wacht">Forum: {$forumcount}</a></li>
							{foreach from=$queues item=queue key=name}
								<li><a href="/tools/query.php?id={$queue->getID()}">{$name|ucfirst}: {$queue->count()}</a></li>
							{/foreach}
						{/if}
						<li><a href="/logout">Log uit</a></li>
					</ul>
				</li>
			{else}
				<li><a href="/">Log in</a></li>
			{/if}
			<li id="cd-lateral-menu-trigger"><span class="cd-lateral-menu-text">Menu</span><span class="cd-lateral-menu-icon"></span></li>
		</ul>
	</nav>
</header>{/strip}