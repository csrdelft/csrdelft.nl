<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.loginform.php
# -------------------------------------------------------------------
# Beeldt loginvakjes af en evt. een opgetreden fout die door
# het target-login-script in de sessie is gezet.
# Als we al ingelogd zijn (= als we nobody zijn) dan wordt een uit-
# loggenknop afgebeeld.
#
# -------------------------------------------------------------------
# Historie:
# 02-01-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');

class LoginForm extends SimpleHTML {

	### private ###

	var $_lid;

	### public ###

	function LoginForm (&$lid) {
		$this->_lid =& $lid;
	}

	function view() {
		
		print("U bent:<br />". htmlentities($this->_lid->getFullName()) . "\n");
		if ($this->_lid->isLoggedIn()) {
			$fSaldo=$this->_lid->getSaldo();
			if($fSaldo<0){
				echo '<br /><br />U staat rood! <br />Uw saldo is: &euro; <span class="bodyrood">'. sprintf ("%01.2f",$fSaldo).'</span><br />';
			}
			print(<<<EOT
<br />
<a href="/leden/profiel/{$this->_lid->getLoginName()}">[P] Mijn profiel</a>

<form id="frm_login" action="/logout.php" method="post">
<p>
<input type="hidden" name="ok_url" value="{$_SERVER["REQUEST_URI"]}" />
<input type="image" src="/images/uitloggen.gif" style="width: 71px; height: 12px;" alt="uitloggen" name="foo" value="bar" />
</p>
</form>
EOT
			);
		} else {
			print(<<<EOT
<form id="frm_login" action="/login.php" method="post">
<p>
<input type="hidden" name="ok_url" value="{$_SERVER["REQUEST_URI"]}" />
<input type="hidden" name="not_ok_url" value="{$_SERVER["REQUEST_URI"]}" />
Naam:<br /><input type="text" name="user" class="tekst" style="width: 140px;" /><br />
Wachtwoord:<br /><input type="password" name="pass" class="tekst" style="width:140px;" /><br />
EOT
			);

			if (isset($_SESSION['auth_error'])) {
				print('<span class="bodyrood">' . htmlspecialchars($_SESSION['auth_error']) . '</span>' . "\n");
				unset($_SESSION['auth_error']);
			}
			print (<<<EOT
<input type="image" src="/images/inloggen.gif" style="width: 68px; height: 12px;" alt="inloggen" name="foo" value="bar" />
</p>
</form>
EOT
			);

		}
		print("\n");
	}
}

?>
