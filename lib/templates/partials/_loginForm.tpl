<ul class="login-form">
    {if $loginlid->hasPermission('P_LOGGED_IN') }
		
    <li id="ingelogd">{if $loginlid->isSued()}
				
        <a href="/endsu/" style="color: red;">{$loginlid->getSuedFrom()->getNaamLink('civitas','html')} als</a><br />
        »
				{/if}
				{$loginlid->getUid()|csrnaam}
			
        <a href="/logout.php" style="margin-left:40px;">uitloggen</a>
        <br />
        <a href="/leden.php">Ga naar de ledenhomepage &raquo;</a>
    </li>
    {else}
		
    <li id="login">{if isset($smarty.session.auth_error)}
				
        <span class="waarschuwing">{$smarty.session.auth_error}</span>
        {/if}
			
        <form action="/login.php" method="post">
            <fieldset>
                <input type="hidden" name="url" value="/leden.php" />
                <input type="text" name="user" value="naam" onfocus="if(this.value=='naam')this.value='';" />
                <input type="password" name="pass" onfocus="this.value='';" value="wachtwoord" />
                <input type="submit" class="submit" name="submit" value="Inloggen &raquo;" />
            </fieldset>
        </form>
    </li>
    <li><a href="account-aanvragen.html">Account aanvragen</a> &raquo;</li>
    {if !isset($smarty.session.auth_error)}
			
    {/if}
		{/if}
</ul>
