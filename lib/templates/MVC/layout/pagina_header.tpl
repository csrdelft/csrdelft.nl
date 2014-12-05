<header>{strip}
	<nav id="cd-top-nav">
		<ul id="cd-horizontal-menu">
			{if LoginModel::mag('P_LOGGED_IN')}
				<li id="cd-ingelogd-menu-toggle"{if LoginModel::instance()->isSued()} class="sued"{/if}>
					<span class="cd-ingelogd-menu-text">{LoginModel::instance()->getLid()->getNaamLink('civitas', 'plain')}</span>
					<ul id="cd-ingelogd-menu">
						{if LoginModel::instance()->isSued()}
							<li><a href="/endsu/" class="error" title="Switch user actie beeindingen">SU {LoginModel::instance()->getSuedFrom()->getNaamLink('civitas', 'plain')}</a></li>
						{/if}
						<li><a href="/communicatie/profiel/{LoginModel::getUid()}" title="Ga naar mijn profiel">Profiel</a></li>
						<li>
							<a href="/communicatie/profiel/{LoginModel::getUid()}#SocCieSaldo" title="Bekijk SocCie saldo historie">
                                {assign var=saldo value=LoginModel::instance()->getLid()->getSoccieSaldo()}
								SocCie: <span{if $saldo < 0} class="staatrood"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
							</a>
						</li>
						<li>
							<a href="/communicatie/profiel/{LoginModel::getUid()}#MaalCieSaldo" title="Bekijk MaalCie saldo historie">
								{assign var=saldo value=LoginModel::instance()->getLid()->getMaalcieSaldo()}
								MaalCie: <span{if $saldo < 0} class="error"{/if}>&euro; {$saldo|number_format:2:",":"."}</span>
							</a>
						</li>
						<li><a href="/instellingen" title="Webstekinstellingen aanpassen">Instellingen</a></li>
						{if LidInstellingen::get('zijbalk', 'favorieten') == 'ja'}
							<li><a href="/menubeheer/toevoegen/favoriet" class="post popup addfav" title="Huidige pagina toevoegen aan favorieten">Favoriet toevoegen</a></li>
						{/if}
						{if LoginModel::mag('P_LEDEN_MOD')}
							{if isset($forumcount)}
								<li><a href="/forum/wacht" title="Aantal forumberichten dat wacht op goedkeuring">Forum: {$forumcount}</a></li>
							{/if}
							{foreach from=$queues item=queue key=name}
								<li><a href="/tools/query.php?id={$queue->getID()}" title="Aantal {$name} dat wacht op goedkeuring">{$name|ucfirst}: {$queue->count()}</a></li>
							{/foreach}
						{/if}
						<li><a href="/logout" title="Uitloggen">Log uit</a></li>
					</ul>
				</li>
			{else}
				<li><a href="/">Log in</a></li>
			{/if}
			<li id="cd-lateral-menu-toggle"><span class="cd-lateral-menu-text">&nbsp;</span><span class="cd-lateral-menu-icon"></span></li>
		</ul>
	</nav>
</header>{/strip}