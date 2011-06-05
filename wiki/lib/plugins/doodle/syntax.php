<?php
/**
 * Doodle Plugin: helps to schedule meetings
 *
 * @license	GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author	 Jonathan Tsai <tryweb@ichiayi.com>  
 * @previsou  author	 Esther Brunner <wikidesign@gmail.com>  
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_doodle extends DokuWiki_Syntax_Plugin {
	/**
	* return some info
	*/
  function getInfo(){
	return array(
		'author' => 'Jonathan Tsai',
		'email'  => 'tryweb@ichiayi.com',
		'date'   => '2009/08/10',
		'name'   => 'Doodle Plugin',
		'desc'   => 'helps to schedule meetings',
		'url'	=> 'http://wiki.splitbrain.org/plugin:doodle',
	);
  }

  function getType(){ return 'substition';}
  function getPType(){ return 'block';}
  function getSort(){ return 168; }
  
	/**
	* Connect pattern to lexer
	*/
  function connectTo($mode){
	$this->Lexer->addSpecialPattern('<doodle.*?>.+?</doodle>', $mode, 'plugin_doodle');
  }

	/**
	* Handle the match
	*/
  function handle($match, $state, $pos, &$handler){
	$match = substr($match, 8, -9);  // strip markup
	list($title, $options) = preg_split('/>/u', $match, 2);
	list($leftstr, $rightstr) = split('\|', $title);
	if ($rightstr == "") {
		$title = $leftstr;
		$disable = "";
		$single = "";
		$login = "";
	}
	else {
		$title = $rightstr;
		$disable = strpos($leftstr, "disable");
		$single = strpos($leftstr, "single");
		$login = strpos($leftstr, "login");
	}
	if (!$options){
		$options = $title;
		$title   = NULL;
	}
	$options = explode('^', $match);
	
	$c = count($options);
	for ($i = 0; $i < $c; $i++){
		$options[$i] = trim($options[$i]);
	}
	
	return array(trim($title), $options, $disable, $single, $login);
  }

	/**
	* Create output
	*/
  function render($mode, &$renderer, $data) {
	if ($mode == 'xhtml'){
		global $lang;

		$options = $data[1];
		$c = count($options)-1;
		$title   = $renderer->_xmlEntities($data[0]);
		$disable   = $renderer->_xmlEntities($data[2]);
		$single   = $renderer->_xmlEntities($data[3]);
		$login   = $renderer->_xmlEntities($data[4]);
		$dID	 = md5($title);
				  
		// prevent caching to ensure the poll results are fresh
		$renderer->info['cache'] = false;
				  
		// get doodle file contents
		$dfile   = metaFN($dID, '.doodle');
		$old_dfile   = metaFN(md5(cleanID($title)), '.doodle');
		// rename old meta File
		if (file_exists($old_dfile)) {
			rename($old_dfile, $dfile);
		}
		$doodle  = unserialize(@file_get_contents($dfile));

		if ($c == 0){
			// no options given: reset the doodle
			$doodle = NULL;
		}
		
		// output the doodle
		$renderer->table_open();
		if ($title){
			$renderer->tablerow_open();
			$renderer->tableheader_open($c);
			$renderer->doc .= $title;
			$renderer->tableheader_close();
			$renderer->tablerow_close();
		}
		$renderer->tablerow_open();
		$renderer->tableheader_open();
		$renderer->doc .= $lang['fullname'];
		$renderer->tableheader_close();
		for ($i = 1; $i < $c; $i++){
			$renderer->tableheader_open();
			$renderer->doc .= $renderer->_xmlEntities($options[$i]);
			$renderer->tableheader_close();
		}
		$renderer->tablerow_close();
					
		if ($submit = $_REQUEST[$dID.'-submit']){
			// user has just voted -> update results
			$user = trim($_REQUEST['fullname']);
			$user = str_replace('<', '&lt;', $user);
			$user = str_replace('>', '&gt;', $user);
			if (!empty($user)){
				for ($i = 1; $i < $c; $i++){
					$opt = md5($options[$i]);
					$opt_old = $renderer->_xmlEntities($options[$i]);
					if (isset($doodle[$user][$opt_old])) {
						$doodle[$user][$opt_old] = false;
					}
					if ($_REQUEST[$dID.'-option'.$i]){
						$doodle[$user][$opt] = true;
					} else {
						$doodle[$user][$opt] = false;
					}
				}
				$doodle[$user]['time']=time();
			}
			$fh = fopen($dfile, 'w');
			fwrite($fh, serialize($doodle));
			fclose($fh);
		}

		// display results
		if (is_array($doodle)) $renderer->doc .= $this->_doodleResults($doodle, $options);
			// display entry form
			if ($disable=="") {
				$renderer->doc .= $this->_doodleForm($c, $dID, $doodle, $options, $login, $single);
			}
			$renderer->table_close();
			return true;
		}

		return false;
	}
  
  function _doodleResults($doodle, $options){
	$cuser = count($doodle);
	if ($cuser < 1) return '';
	$copt  = count($options)-1;
	$users = array_keys($doodle);
	$ret   = '';
	$count = array();

	// table okay / not okay
	for ($i = 0; $i < $cuser; $i++){
		$isChecked = 0;
		$user = $users[$i];
		$updTime = isset($doodle[$user]['time'])?date('Y-m-d H:i:s', $doodle[$user]['time']):'Okey';
		$retTmp = '<tr><td class="rightalign">'.$user.'</td>';
		for ($j = 1; $j < $copt; $j++){
			$option = md5($options[$j]);
			$option_old = $options[$j];
			if ($doodle[$user][$option] || $doodle[$user][$option_old]){
				$class = 'okay';
				$title = '<img src="'.DOKU_BASE.'lib/images/success.png" title="'.
				  $updTime.'" alt="'.$updTime.'" '.
				  'width="16" height="16" />';
				$count[$option] += 1;
				$isChecked = 1;
			} elseif (!isset($doodle[$user][$option]) && !isset($doodle[$user][$option_old])){
				$class = 'centeralign';
				$title = '&nbsp;';
			} else {
				$class = 'notokay';
				$title = '&nbsp;';
			}
			$retTmp .= '<td class="'.$class.'">'.$title.'</td>';
		}
		$retTmp .= '</tr>';
		$ret .= ($isChecked==1)?$retTmp:"";
	}

	// total count
	$ret .= '<tr><td>&nbsp;</td>';
	for ($j = 1; $j < $copt; $j++){
		$option = md5($options[$j]);
		$ret .= '<td class="centeralign">'.$count[$option].'</td>';
	}
	$ret .= '</tr>';

	return $ret;
  }
  
  function _doodleForm($n, $dID, $doodle, $options, $login, $single){
	global $lang;
	global $ID;
	global $INFO;

	$user  = ($_SERVER['REMOTE_USER'] ? $INFO['userinfo']['name'] : '');
	$count = array();

	$ret = '<form id="doodle__form" method="post" action="'.script().
		'" accept-charset="'.$lang['encoding'].'"><tr>'.
		'<input type="hidden" name="do" value="show" />'.
		'<input type="hidden" name="id" value="'.$ID.'" />';
	if ($login=="") {
		$ret .= '<td class="rightalign"><input type="text" name="fullname" '.'value="'.$user.'" /></td>';
	}
	else {
		if ($user=="") {
			return "";
		}
		$ret .= '<input type="hidden" name="fullname" value="'.$user.'" />'.'<td class="rightalign">'.$user.'</td>';
	}
	$i = 1;
	while ($i < $n){
		if (is_array($doodle)){
			$option = md5($options[$i]);
			$option_old = $options[$j];
			if ($doodle[$user][$option] || $doodle[$user][$option_old]){
				$checked = 'checked="checked" ';
			} else {
				$checked = ' ';
			}
		} else {
			$checked = ' ';
		}
		$onclickstr = "";
		if ($single!="") {
			$onclickstr = 'onclick="javascript:';
			for ($j=1;$j<$n;$j++) {
				if ($j!=$i) {
					$onclickstr .= 'form[\''.$dID.'-option'.$j.'\'].checked=false;';
				}
			}
			$onclickstr .= '"';
		}
		$ret.= '<td class="centeralign"><input type="checkbox" '.
			'name="'.$dID.'-option'.$i.'" value="1" '.$checked.' '.$onclickstr.'/></td>';
		$i++;
	}
	$ret .= '</tr><tr><td class="centeralign" colspan="'.($n).'">'.
		'<input class="button" type="submit" name="'.$dID.'-submit" '.
		'value="'.$this->getLang('btn_submit').'" />'.
		'</td></tr></form>';

	return $ret;
  }
}

?>
