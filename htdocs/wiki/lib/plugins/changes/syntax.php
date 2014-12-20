<?php
/**
 * Changes Plugin: List the most recent changes of the wiki
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Mykola Ostrovskyy <spambox03@mail.ru>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class syntax_plugin_changes
 */
class syntax_plugin_changes extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    public function getType() {
        return 'substition';
    }

    /**
     * What type of XHTML do we create?
     */
    public function getPType() {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort() {
        return 105;
    }

    /**
     * Connect pattern to lexer
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{changes>[^}]*\}\}', $mode, 'plugin_changes');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = substr($match, 10, -2);

        $data = array(
            'ns' => array(),
            'excludedpages' => array(),
            'count' => 10,
            'type' => array(),
            'render' => 'list',
            'render-flags' => array(),
            'maxage' => null
        );

        $match = explode('&', $match);
        foreach($match as $m) {
            if(is_numeric($m)) {
                $data['count'] = (int) $m;
            } else {
                if(preg_match('/(\w+)\s*=(.+)/', $m, $temp) == 1) {
                    $this->handleNamedParameter($temp[1], trim($temp[2]), $data);
                } else {
                    $this->addNamespace($data, trim($m));
                }
            }
        }

        return $data;
    }

    /**
     * Handle parameters that are specified uing <name>=<value> syntax
     */
    protected function handleNamedParameter($name, $value, &$data) {
        global $ID;

        static $types = array('edit' => 'E', 'create' => 'C', 'delete' => 'D', 'minor' => 'e');
        static $renderers = array('list', 'pagelist');

        switch($name) {
            case 'count':
                $data[$name] = intval($value);
                break;
            case 'ns':
                foreach(preg_split('/\s*,\s*/', $value) as $value) {
                    $this->addNamespace($data, $value);
                }
                break;
            case 'type':
                foreach(preg_split('/\s*,\s*/', $value) as $value) {
                    if(array_key_exists($value, $types)) {
                        $data[$name][] = $types[$value];
                    }
                }
                break;
            case 'render':
                // parse "name(flag1, flag2)" syntax
                if(preg_match('/(\w+)(?:\((.*)\))?/', $value, $match) == 1) {
                    if(in_array($match[1], $renderers)) {
                        $data[$name] = $match[1];
                        $flags = trim($match[2]);
                        if($flags != '') {
                            $data['render-flags'] = preg_split('/\s*,\s*/', $flags);
                        }
                    }
                }
                break;
            case 'user':
                foreach(preg_split('/\s*,\s*/', $value) as $value) {
                    $data[$name][] = $value;
                }
                break;
            case 'excludedpages':
                foreach(preg_split('/\s*,\s*/', $value) as $page) {
                    if(!empty($page)) {
                        resolve_pageid(getNS($ID), $page, $exists);
                        $data[$name][] = $page;
                    }
                }
                break;
            case 'maxage':
                $data[$name] = intval($value);
                break;
        }
    }

    /**
     * Clean-up the namespace name and add it (if valid) into the $data array
     */
    protected function addNamespace(&$data, $namespace) {
        $action = ($namespace{0} == '-') ? 'exclude' : 'include';
        $namespace = cleanID(preg_replace('/^[+-]/', '', $namespace));
        if(!empty($namespace)) {
            $data['ns'][$action][] = $namespace;
        }
    }

    /**
     * Handles the actual output creation.
     *
     * @param   $mode   string        output format being rendered
     * @param   $R Doku_Renderer the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    public function render($mode, Doku_Renderer $R, $data) {

        if($mode == 'xhtml') {
            /* @var Doku_Renderer_xhtml $R */
            $R->info['cache'] = false;
            $changes = $this->getChanges($data['count'], $data['ns'], $data['excludedpages'], $data['type'], $data['user'], $data['maxage']);
            if(!count($changes)) return true;

            switch($data['render']) {
                case 'list':
                    $this->renderSimpleList($changes, $R, $data['render-flags']);
                    break;
                case 'pagelist':
                    $this->renderPageList($changes, $R, $data['render-flags']);
                    break;
            }
            return true;

        } elseif($mode == 'metadata') {
            /* @var Doku_Renderer_metadata $R */
            global $conf;
            $R->meta['relation']['depends']['rendering'][$conf['changelog']] = true;
            return true;
        }
        return false;
    }

    /**
     * Based on getRecents() from inc/changelog.php
     *
     * @param int   $num
     * @param array $ns
     * @param array $excludedpages
     * @param array $type
     * @param array $user
     * @param int   $maxage
     * @return array
     */
    protected function getChanges($num, $ns, $excludedpages, $type, $user, $maxage) {
        global $conf;
        $changes = array();
        $seen = array();
        $count = 0;
        $lines = @file($conf['changelog']);

        // Merge media changelog
        if($this->getConf('listmedia')) {
            $linesMedia = @file($conf['media_changelog']);
            // Add a tag to identiy the media lines
            foreach($linesMedia as $key => $value) {
                $linesMedia[$key] = $value . "media";
            }
            $lines = array_merge($lines, $linesMedia);
        }

        if(is_null($maxage)) $maxage = (int) $this->getConf('maxage');

        for($i = count($lines) - 1; $i >= 0; $i--) {
            $change = $this->handleChangelogLine($lines[$i], $ns, $excludedpages, $type, $user, $maxage, $seen);
            if($change !== false) {
                $changes[] = $change;
                // break when we have enough entries
                if(++$count >= $num) break;
            }
        }

        // Date sort merged page and media changes
        if($this->getConf('listmedia')) {
            $dates = array();
            foreach($changes as $change) {
                $dates[] = $change['date'];
            }
            array_multisort($dates, SORT_ASC, $changes);
        }

        return $changes;
    }

    /**
     * Based on _handleRecent() from inc/changelog.php
     *
     * @param string $line
     * @param array  $ns
     * @param array  $excludedpages
     * @param array  $type
     * @param array  $user
     * @param int    $maxage
     * @param array  $seen
     * @return array|bool
     */
    protected function handleChangelogLine($line, $ns, $excludedpages, $type, $user, $maxage, &$seen) {
        // split the line into parts
        $change = parseChangelogLine($line);
        if($change === false) return false;

        // skip seen ones
        if(isset($seen[$change['id']])) return false;

        // filter type
        if(!empty($type) && !in_array($change['type'], $type)) return false;

        // filter user
        if(!empty($user) && (empty($change['user']) || !in_array($change['user'], $user))) return false;

        // remember in seen to skip additional sights
        $seen[$change['id']] = 1;

        // show only not existing pages for delete
        if($change['type'] != 'D' && !page_exists($change['id'])) return false;

        // filter maxage
        if($maxage && $change['date'] < (time() - $maxage)) {
            return false;
        }

        // check if it's a hidden page
        if(isHiddenPage($change['id'])) return false;

        // filter included namespaces
        if(isset($ns['include'])) {
            if(!$this->isInNamespace($ns['include'], $change['id'])) return false;
        }

        // filter excluded namespaces
        if(isset($ns['exclude'])) {
            if($this->isInNamespace($ns['exclude'], $change['id'])) return false;
        }
        // exclude pages
        if(!empty($excludedpages)) {
            foreach($excludedpages as $page) {
                if($change['id'] == $page) return false;
            }
        }

        // check ACL
        $change['perms'] = auth_quickaclcheck($change['id']);
        if($change['perms'] < AUTH_READ) return false;

        return $change;
    }

    /**
     * Check if page belongs to one of namespaces in the list
     *
     * @param array $namespaces
     * @param string $id page id
     * @return bool
     */
    protected function isInNamespace($namespaces, $id) {
        foreach($namespaces as $ns) {
            if((strpos($id, $ns . ':') === 0)) return true;
        }
        return false;
    }

    /**
     * Render via the Pagelist plugin
     *
     * @param $changes
     * @param Doku_Renderer_xhtml $R
     * @param $flags
     */
    protected function renderPageList($changes, &$R, $flags) {
        /** @var helper_plugin_pagelist $pagelist */
        $pagelist = @plugin_load('helper', 'pagelist');
        if($pagelist) {
            $pagelist->setFlags($flags);
            $pagelist->startList();
            foreach($changes as $change) {
                $page['id'] = $change['id'];
                $page['date'] = $change['date'];
                $page['user'] = $this->getUserName($change);
                $page['desc'] = $change['sum'];
                $pagelist->addPage($page);
            }
            $R->doc .= $pagelist->finishList();
        } else {
            // Fallback to the simple list renderer
            $this->renderSimpleList($changes, $R);
        }
    }

    /**
     * Render the day header
     *
     * @param Doku_Renderer $R
     * @param int $date
     */
    protected function dayheader(&$R, $date) {
        if($R->getFormat() == 'xhtml') {
            /* @var Doku_Renderer_xhtml $R  */
            $R->doc .= '<h3 class="changes">';
            $R->cdata(dformat($date, $this->getConf('dayheaderfmt')));
            $R->doc .= '</h3>';
        } else {
            $R->header(dformat($date, $this->getConf('dayheaderfmt')), 3, 0);
        }
    }

    /**
     * Render with a simple list render
     *
     * @param array $changes
     * @param Doku_Renderer_xhtml $R
     * @param null $flags
     */
    protected function renderSimpleList($changes, &$R, $flags = null) {
        global $conf;
        $flags = $this->parseSimpleListFlags($flags);

        $date = '';
        if($flags['dayheaders']) {
            $date = date('Ymd', $changes[0]['date']);
            $this->dayheader($R, $changes[0]['date']);
        }

        $R->listu_open();
        foreach($changes as $change) {
            if($flags['dayheaders']) {
                $tdate = date('Ymd', $change['date']);
                if($tdate != $date) {
                    $R->listu_close(); // break list to insert new header
                    $this->dayheader($R, $change['date']);
                    $R->listu_open();
                    $date = $tdate;
                }
            }

            $R->listitem_open(1);
            $R->listcontent_open();
            if(trim($change['extra']) == 'media') {
                $R->internalmedia(':' . $change['id'], null, null, false, 'navigation');
            } else {
                $R->internallink(':' . $change['id'], null, null, false, 'navigation');
            }
            if($flags['summary']) {
                $R->cdata(' ' . $change['sum']);
            }
            if($flags['signature']) {
                $user = $this->getUserName($change);
                $date = strftime($conf['dformat'], $change['date']);
                $R->cdata(' ');
                $R->entity('---');
                $R->cdata(' ');
                $R->emphasis_open();
                $R->cdata($user . ' ' . $date);
                $R->emphasis_close();
            }
            $R->listcontent_close();
            $R->listitem_close();
        }
        $R->listu_close();
    }

    /**
     * Parse flags for the simple list render
     *
     * @param array $flags
     * @return array
     */
    protected function parseSimpleListFlags($flags) {
        $outFlags = array('summary' => true, 'signature' => false, 'dayheaders' => false);
        if(!empty($flags)) {
            foreach($flags as $flag) {
                if(array_key_exists($flag, $outFlags)) {
                    $outFlags[$flag] = true;
                } elseif(substr($flag, 0, 2) == 'no') {
                    $flag = substr($flag, 2);
                    if(array_key_exists($flag, $outFlags)) {
                        $outFlags[$flag] = false;
                    }
                }
            }
        }
        return $outFlags;
    }

    /**
     * Get username or fallback to ip
     *
     * @param array $change
     * @return mixed
     */
    protected function getUserName($change) {
        /* @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        if(!empty($change['user'])) {
            $user = $auth->getUserData($change['user']);
            return $user['name'];
        } else {
            return $change['ip'];
        }
    }
}

