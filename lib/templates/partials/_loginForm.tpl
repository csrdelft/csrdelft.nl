<form action="/login.php" method="post">
	<fieldset>
		<input type="hidden" name="url" value="/leden.php" />
		<input class="text" type="text" name="user" value="Bijnaam of lidnummer" />
		<input class="text" type="password" name="pass" value="wachtwoord" />
		<input class="submit" type="submit" name="login" value="Inloggen" />
	</fieldset>{if isset($smarty.session.auth_error)}
	<p class="error">Login gefaald!</p>{/if}
</form>
<ul>
	<li><a href="#" class="login-submit">Inloggen</a> &raquo;</li>
	<li><a href="/account-aanvragen">Account aanvragen</a> &raquo;</li>
</ul>
{*
<ul class="login-form">

    <li id="login">{if isset($smarty.session.auth_error)}

        <span class="waarschuwing">{$smarty.session.auth_error}</span>
        {/if}

        <form action="/login.php" method="post">
            <fieldset>
                <input type="hidden" name="url" value="/leden.php" />
                <input type="text" name="user" value="Bijnaam of lidnummer" onfocus="if(this.value=='Bijnaam of lidnummer')this.value='';" />
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
*}