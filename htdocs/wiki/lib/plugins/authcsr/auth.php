<?php

/**
 * DokuWiki Plugin authcsr (Auth Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
	die();
}

class auth_plugin_authcsr extends DokuWiki_Auth_Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(); // for compatibility
		// set capabilities accordingly
		//$this->cando['modLogin']    => false; // can login names be changed?
		//$this->cando['modPass']     => false; // can passwords be changed?
		//$this->cando['modName']     => false; // can real names be changed?
		//$this->cando['modMail']     => false; // can emails be changed?
		//$this->cando['modGroups']   => false; // can groups be changed?
		//$this->cando['getUsers']    => false; // can a (filtered) list of users be retrieved?
		//$this->cando['getUserCount']=> false; // can the number of users be retrieved?
		//$this->cando['getGroups']   => false; // can a list of available groups be retrieved?
		$this->cando['external'] = true;
		$this->cando['logoff'] = true;

		//intialize your auth system and set success to true, if successful
		$this->success = true;
	}

	/**
	 * Do all authentication
	 *
	 * Set $this->cando['external'] = true when implemented
	 *
	 * If this function is implemented it will be used to
	 * authenticate a user - all other DokuWiki internals
	 * will not be used for authenticating, thus
	 * implementing the checkPass() function is not needed
	 * anymore.
	 *
	 * The function can be used to authenticate against third
	 * party cookies or Apache auth mechanisms and replaces
	 * the auth_login() function
	 *
	 * The function will be called with or without a set
	 * username. If the Username is given it was called
	 * from the login form and the given credentials might
	 * need to be checked. If no username was given it
	 * the function needs to check if the user is logged in
	 * by other means (cookie, environment).
	 *
	 * The function needs to set some globals needed by
	 * DokuWiki like auth_login() does.
	 *
	 * @see auth_login()
	 *
	 * Controleert via code van de standaard C.S.R.-site of ingelogde geen
	 * nobody is, vervolgens kunnen gegegens worden opgehaald.
	 * Als er inloggegevens worden meegegeven wordt gepoogd in te loggen.
	 *
	 * @param   string  $user    Username (uid or nickname)
	 * @param   string  $pass    Cleartext Password
	 * @param   bool    $sticky  Cookie should not expire. Not used.
	 * @return  bool             true on successful auth
	 */
	function trustExternal($user, $pass, $sticky = false) {
		global $USERINFO;
		global $lang;
		global $conf;

		// als er een gebruiker is gegeven willen we graag proberen in te loggen via inlogformulier
		if (!empty($user)) {

			// login with:
			// - x999
			// - or as lid when:
			//      * cookie available
			//      * private_token was added to url (checking the permissions by LoginModel::hasPermission, needs setting allowPrivateUrl to true)

			if (LoginModel::instance()->login(strval($user), strval($pass))) {
				//success
			} else {
				//invalid credentials - log off
				msg($lang['badlogin'], -1);
				auth_logoff();
				return false;
			}
		}

		// als ingelogd genoeg permissies heeft gegevens ophalen en bewaren
		if (LoginModel::mag('P_LOGGED_IN,groep:wikitoegang', false)
				OR ( LoginModel::mag('P_LOGGED_IN,groep:wikitoegang', true) AND $_SERVER['PHP_SELF'] == '/wiki/feed.php')
		) {

			// okay we're logged in - set the globals
			$account = LoginModel::getAccount();
			$USERINFO['name'] = ProfielModel::getNaam($account->uid, 'civitas');
			$USERINFO['mail'] = $account->email;
			$USERINFO['grps'] = GroepenModel::getWikiToegang($account->uid);
			// always add the default group to the list of groups
			if (!in_array($conf['defaultgroup'], $USERINFO['grps'])) {
				$USERINFO['grps'][] = $conf['defaultgroup'];
			}

			$_SERVER['REMOTE_USER'] = $account->uid;
			$_SESSION[DOKU_COOKIE]['auth']['user'] = $account->uid;
			$_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
			return true;

			# example:
			#    // set the globals if authed
			#    $USERINFO['name'] = 'FIXME'; //real name
			#    $USERINFO['mail'] = 'FIXME';
			#    $USERINFO['grps'] = array('FIXME');
			#    $_SERVER['REMOTE_USER'] = $user; //username=uid
			#    $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
			#    $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
			#    $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
			#    return true;
		}

		if (LoginModel::getUid() != 'x999') {
			msg('Niet genoeg permissies', -1);
		}
		// to be sure
		auth_logoff();
		return false;
	}

	/**
	 * Log off the current user. Remove cookie and login as nobody.
	 *
	 * Is run in addition to the ususal logoff method. Should
	 * only be needed when trustExternal is implemented.
	 *
	 * @see     auth_logoff()
	 */
	function logOff() {
		LoginModel::instance()->logout();
	}

	/**
	 * Return user info [required function]
	 *
	 * Returns info about the given user needs to contain
	 * at least these fields:
	 *
	 * name string  full name of the user
	 * mail string  email addres of the user
	 * grps array   list of groups the user is in
	 *
	 * @param   string $useruid the user name
	 * @return  array containing user data or false
	 */
	function getUserData($useruid) {
		global $conf;

		if (AccountModel::isValidUid($useruid)) {
			$profiel = ProfielModel::get($useruid);
			if ($profiel) {
				$info['name'] = $profiel->getNaam();
				$info['mail'] = $profiel->getPrimaryEmail();
				$info['grps'] = GroepenModel::getWikiToegang($useruid);
				// always add the default group to the list of groups
				if (!in_array($conf['defaultgroup'], $info['grps']) AND $useruid != 'x999') {
					$info['grps'][] = $conf['defaultgroup'];
				}

				return $info;
			}
		}
		return false;
	}

	/**
	 * Modify user data [implement only where required/possible]
	 *
	 * Set the mod* capabilities according to the implemented features
	 *
	 * @param   string $user    nick of the user to be changed
	 * @param   array  $changes array of field/value pairs to be changed (password will be clear text)
	 * @return  bool
	 */
	//public function modifyUser($user, $changes) {
	// FIXME implement
	//    return false;
	//}


	/**
	 * Bulk retrieval of user data [implement only where required/possible]
	 *
	 * Set getUsers capability when implemented
	 *
	 * @param   int   $start     index of first user to be returned
	 * @param   int   $limit     max number of users to be returned
	 * @param   array $filter    array of field/pattern pairs, null for no filter
	 * @return  array list of userinfo (refer getUserData for internal userinfo details)
	 */
	//public function retrieveUsers($start = 0, $limit = -1, $filter = null) {
	// FIXME implement
	//    return array();
	//}

	/**
	 * Return a count of the number of user which meet $filter criteria
	 * [should be implemented whenever retrieveUsers is implemented]
	 *
	 * Set getUserCount capability when implemented
	 *
	 * @param  array $filter array of field/pattern pairs, empty array for no filter
	 * @return int
	 */
	//public function getUserCount($filter = array()) {
	// FIXME implement
	//    return 0;
	//}

	/**
	 * Define a group [implement only where required/possible]
	 *
	 * Set addGroup capability when implemented
	 *
	 * @param   string $group
	 * @return  bool
	 */
	//public function addGroup($group) {
	// FIXME implement
	//    return false;
	//}

	/**
	 * Retrieve groups [implement only where required/possible]
	 *
	 * Set getGroups capability when implemented
	 *
	 * @param   int $start
	 * @param   int $limit
	 * @return  array
	 */
	//public function retrieveGroups($start = 0, $limit = 0) {
	// FIXME implement
	//    return array();
	//}

	/**
	 * Return case sensitivity of the backend
	 *
	 * When your backend is caseinsensitive (eg. you can login with USER and
	 * user) then you need to overwrite this method and return false
	 *
	 * @return bool
	 */
	public function isCaseSensitive() {
		return true;
	}

	/**
	 * Sanitize a given username
	 *
	 * This function is applied to any user name that is given to
	 * the backend and should also be applied to any user name within
	 * the backend before returning it somewhere.
	 *
	 * This should be used to enforce username restrictions.
	 *
	 * @param string $user username
	 * @return string the cleaned username
	 */
	public function cleanUser($user) {
		return $user;
	}

	/**
	 * Sanitize a given groupname
	 *
	 * This function is applied to any groupname that is given to
	 * the backend and should also be applied to any groupname within
	 * the backend before returning it somewhere.
	 *
	 * This should be used to enforce groupname restrictions.
	 *
	 * Groupnames are to be passed without a leading '@' here.
	 *
	 * @param  string $group groupname
	 * @return string the cleaned groupname
	 */
	public function cleanGroup($group) {
		return $group;
	}

}

// vim:ts=4:sw=4:et: