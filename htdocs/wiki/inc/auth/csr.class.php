<?php
/**
 * C.S.R. authentication backend.
 *
 * ------------------------------------------------
 * Wijzig in inc/init.php vanaf regel 148, nodig voor session_start(),
 * de instellingen voor de cookie zodat cookie overeenkomt met cookies
 * van C.S.R.-stek.
 *
 * Aanpassing:
    //bij authenticatie via C.S.R.-site andere instellingen voor de sessiecookie
    if($conf['authtype']=='csr'){
        session_name("PHPSESSID");
        $sessiepath = fullpath(dirname(__FILE__).'/../../../').'/sessie';
        session_save_path($sessiepath);
        session_set_cookie_params(1036800, '/', '', false,false);
    }else{
        //Defaults of Dokuwiki
        session_name("DokuWiki");
        if (version_compare(PHP_VERSION, '5.2.0', '>')) {
            session_set_cookie_params(0,DOKU_REL,'',($conf['securecookie'] && is_ssl()),true);
        }else{
            session_set_cookie_params(0,DOKU_REL,'',($conf['securecookie'] && is_ssl()));
        }
    }
 * ------------------------------------------------
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gerrit Uitslag
 */

class auth_csr extends auth_basic {

    /**
     * Constructor
     *
     * Set capabilities.
     */
    function auth_csr() {
        $this->cando['external'] = true;
        $this->cando['logoff']   = true;
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

        # als er een gebruiker is gegeven willen we graag proberen in te loggen via inlogformulier
        if(!empty($user)) {
            if(LoginModel::instance()->login(strval($user), strval($pass), $checkip = false) AND LoginModel::getUid() != 'x999') {
                //success
            } else {
                //invalid credentials - log off
                msg($lang['badlogin'], -1);
                auth_logoff();
                return false;
            }
        }

        # als ingelogd genoeg permissies heeft gegevens ophalen en bewaren
        if(LoginModel::mag('P_LOGGED_IN,groep:wikitoegang', $token_authorizable = false)
            OR (LoginModel::mag('P_LOGGED_IN,groep:wikitoegang', $token_authorizable = true) AND $_SERVER['PHP_SELF'] == '/wiki/feed.php')
        ) {

            // okay we're logged in - set the globals
            require_once 'groepen/groep.class.php';
            $lid                 = LoginModel::instance()->getLid();
            $USERINFO['name']    = $lid->getNaam();
            $USERINFO['mail']    = $lid->getEmail();
            $USERINFO['pasfoto'] = $lid->getPasfoto($imgTag = false);
            $USERINFO['grps']    = Groepen::getWikigroupsByUid($lid->getUid());
            // always add the default group to the list of groups
            if(!in_array($conf['defaultgroup'], $USERINFO['grps'])) {
                $USERINFO['grps'][] = $conf['defaultgroup'];
            }

            $_SERVER['REMOTE_USER']                = $lid->getUid();
            $_SESSION[DOKU_COOKIE]['auth']['user'] = $lid->getUid();
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

        if(LoginModel::getUid() != 'x999') {
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
     */
    function getUserData($useruid) {
        global $conf;

        if(Lid::isValidUid($useruid)) {
            $lid = LidCache::getLid($useruid);
            if($lid instanceof Lid) {
                $info['name']    = $lid->getNaam();
                $info['mail']    = $lid->getEmail();
                $info['pasfoto'] = $lid->getPasfoto($imgTag = false);
                require_once 'groepen/groep.class.php';
                $info['grps'] = Groepen::getWikigroupsByUid($useruid);
                // always add the default group to the list of groups
                if(!in_array($conf['defaultgroup'], $info['grps']) AND $useruid != 'x999') {
                    $info['grps'][] = $conf['defaultgroup'];
                }

                return $info;
            }
        }
        return false;
    }

}

//Setup VIM: ex: et ts=2 :
