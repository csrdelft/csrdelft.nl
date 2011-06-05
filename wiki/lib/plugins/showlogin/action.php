<?php
/**
 * Dokuwiki Action Plugin: Show Login-Page on "Access Denied"
 * 
 * @author Oliver Geisen <oliver.geisen@kreisbote.de>
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
 
class action_plugin_showlogin extends DokuWiki_Action_Plugin {

  /**
   * return some info
   */
  function getInfo(){
    return array(
      'author' => 'Oliver Geisen',
      'email'  => 'oliver.geisen@kreisbote.de',
      'date'   => '2008-04-17',
      'name'   => 'Show Login',
      'desc'   => 'If access to page is denied, show login-form to users not already logged in.',
      'url'    => 'http://inet-sv1.kreisbote.de/downloads/showlogin.zip',
    );
  }

  /**
   * Register its handlers with the dokuwiki's event controller
   */
  function register(&$controller) {
    # TPL_CONTENT_DISPLAY is called before and after content of wikipage
    # is written to output buffer
    $controller->register_hook(
      'TPL_CONTENT_DISPLAY', 'AFTER', $this, 'append_to_content'
    );
  }

  /**
   * Handle the event
   */ 
  function append_to_content(&$event, $param) {
    global $ACT;
    global $ID;

    # add login form to page, only on access denied
    # and if user is not logged in
    if (($ACT == 'denied') && (! $_SERVER['REMOTE_USER'])) {
      html_login();
    }
  }
}

