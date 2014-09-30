<header>
	<a id="cd-logo" href="/"><div id="beeldmerk"></div></a>{* TODO: Broodkruimels *}
	<nav id="cd-top-nav">
		<ul>
			{if LoginModel::mag('P_LOGGED_IN')}
				<li id="ingelogd">
					<div id="uitloggen"><a href="/logout">Log uit</a></div>
					{if LoginModel::instance()->isSued()}
						<a href="/endsu/" style="color: red;">{LoginModel::instance()->getSuedFrom()->getNaamLink('civitas', 'plain')} als</a><br />»
					{/if}
					{LoginModel::getUid()|csrnaam}<br />
					<div id="saldi">
						{foreach from=LoginModel::instance()->getLid()->getSaldi() item=saldo}
							<div class="saldoregel">
								<div class="saldo{if $saldo.saldo < 0 AND LoginModel::getUid()!='0524'} staatrood{/if}">&euro; {$saldo.saldo|number_format:2:",":"."}</div>
								{$saldo.naam}:
							</div>
						{/foreach}
					</div>
					{if LoginModel::mag('P_LEDEN_MOD')}
						<div id="adminding">
							Beheer
							{if LoginModel::mag('P_ADMIN')}
								{if $forumcount > 0 OR $queues.meded->count()>0}
									({$forumcount}/{$queues.meded->count()})
								{/if}
							{/if}
							<div>
								{if LoginModel::mag('P_ADMIN')}
									<span class="queues">
										<a href="/forum/wacht">Forum: <span class="count">{$forumcount}</span><br /></a>
											{foreach from=$queues item=queue key=name}
											<a href="/tools/query.php?id={$queue->getID()}">
												{$name|ucfirst}: <span class="count">{$queue->count()}</span><br />
											</a>
										{/foreach}
									</span>
									{if $smarty.const.DEBUG}
										<a href="/su/x101">&raquo; SU Jan Lid.</a><br />
									{/if}
								{/if}
								<a href="/tools/query.php">&raquo; Opgeslagen queries</a><br />
								<a href="/beheer">&raquo; Beheeroverzicht</a><br />
							</div>
						</div>
						{literal}
							<script>
								jQuery(document).ready(function ($) {
									$('#adminding').click(function () {
										$(this).children('div').toggle();
									});
									$('#adminding div').hide();
								});
							</script>
						{/literal}
					{/if}
				</li>
			{else}
				<li><a href="/">Log in</a></li>
				{/if}
		</ul>
	</nav>
	<a id="cd-menu-trigger" href="#0"><span class="cd-menu-text">Menu</span><span class="cd-menu-icon"></span></a>
</header>