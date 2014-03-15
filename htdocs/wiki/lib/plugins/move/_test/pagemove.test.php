<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for the move plugin
 */
class plugin_move_pagemove_test  extends DokuWikiTest {

    var $movedToId = '';
    var $movedId = 'parent_ns:current_ns:test_page';
    var $parentBacklinkingId = 'parent_ns:some_page';
    var $currentNsBacklinkingId = 'parent_ns:current_ns:some_page';
    var $otherBacklinkingId = 'level0:level1:other_backlinking_page';
    var $subNsPage = 'parent_ns:current_ns:sub_ns:some_page';
    /** @var helper_plugin_move $move */
    private $move = NULL;

    // @todo Move page to an ID which already exists
    // @todo Check backlinks of a sub-namespace page (moving same, up, down, different)

    function setUp() {
        $this->pluginsEnabled[] = 'move';
        global $ID;
        global $INFO;

        $ID = $this->movedId;

        $text = <<<EOT
[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
        $summary = 'Test';
        saveWikiText($this->movedId, $text, $summary);
        $INFO = pageinfo();

        $references = array_keys(p_get_metadata($this->movedId, 'relation references', METADATA_RENDER_UNLIMITED));
        idx_get_indexer()->addMetaKeys($this->movedId, 'relation_references', $references);

        $text = <<<EOT
[[$this->movedId|$this->movedId]]
[[:$this->movedId|:$this->movedId]]
[[.current_ns:test_page|.current_ns:test_page]]
[[.:current_ns:test_page|.:current_ns:test_page]]
[[..parent_ns:current_ns:test_page|..parent_ns:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
        saveWikiText($this->parentBacklinkingId, $text, $summary);
        $references = array_keys(p_get_metadata($this->parentBacklinkingId, 'relation references', METADATA_RENDER_UNLIMITED));
        idx_get_indexer()->addMetaKeys($this->parentBacklinkingId, 'relation_references', $references);

        $text = <<<EOT
[[$this->movedId|$this->movedId]]
[[:$this->movedId|:$this->movedId]]
[[..current_ns:test_page|..current_ns:test_page]]
[[..:current_ns:test_page|..:current_ns:test_page]]
[[test_page|test_page]]
[[.test_page|.test_page]]
[[.:test_page|.:test_page]]
[[..test_page|..test_page]]
[[..:test_page|..:test_page]]
[[.:..:test_page|.:..:test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
        saveWikiText($this->currentNsBacklinkingId, $text, $summary);
        $references = array_keys(p_get_metadata($this->currentNsBacklinkingId, 'relation references', METADATA_RENDER_UNLIMITED));
        idx_get_indexer()->addMetaKeys($this->currentNsBacklinkingId, 'relation_references', $references);

        $text = <<<EOT
[[$this->movedId|$this->movedId]]
[[:$this->movedId|:$this->movedId]]
[[.current_ns:test_page|.current_ns:test_page]]
[[.:current_ns:test_page|.:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
        saveWikiText($this->otherBacklinkingId, $text, $summary);
        $references = array_keys(p_get_metadata($this->otherBacklinkingId, 'relation references', METADATA_RENDER_UNLIMITED));
        idx_get_indexer()->addMetaKeys($this->otherBacklinkingId, 'relation_references', $references);

        $text = <<<EOT
[[$this->movedId|$this->movedId]]
[[:$this->movedId|:$this->movedId]]
[[..:..current_ns:test_page|..:..current_ns:test_page]]
[[..:..:current_ns:test_page|..:..:current_ns:test_page]]
[[test_page|test_page]]
[[..:test_page|..:test_page]]
[[..:test_page|..:test_page]]
[[.:..:test_page|.:..:test_page]]
[[new_page|new_page]]
[[..:new_page|..:new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
        saveWikiText($this->subNsPage, $text, $summary);
        $references = array_keys(p_get_metadata($this->subNsPage, 'relation references', METADATA_RENDER_UNLIMITED));
        idx_get_indexer()->addMetaKeys($this->subNsPage, 'relation_references', $references);

        $this->move = new helper_plugin_move();
        parent::setUp();
    }

#	function testPagemove() {
#		$this->assertEqual(1,1);
#	}

// 	function test_pm_getforwardlinks() {
// 		$origLinkAbsLinkArray = $this->move->_pm_getforwardlinks($this->movedId);
// 	}

	function test_move_page_in_same_ns() {
	    global $ID;

	    $newPagename = 'new_page';

	    $opts = array();
	    $opts['page_ns'] = 'page';
	    $opts['ns']   = getNS($ID);
        $opts['name'] = noNS($ID);
        $opts['newns'] = $opts['ns'];
        $opts['newname'] = $newPagename;
        $this->movedToId = $opts['newns'].':'.$newPagename;
	    $this->move->move_page($opts);

	    $newId = $opts['newns'].':'.$opts['newname'];
	    $newContent = rawWiki($newId);
	    $expectedContent = <<<EOT
[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->parentBacklinkingId);
	    $expectedContent = <<<EOT
[[.:current_ns:new_page|$this->movedId]]
[[.:current_ns:new_page|:$this->movedId]]
[[.:current_ns:new_page|.current_ns:test_page]]
[[.:current_ns:new_page|.:current_ns:test_page]]
[[.:current_ns:new_page|..parent_ns:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->currentNsBacklinkingId);
	    $expectedContent = <<<EOT
[[new_page|$this->movedId]]
[[new_page|:$this->movedId]]
[[new_page|..current_ns:test_page]]
[[new_page|..:current_ns:test_page]]
[[new_page|test_page]]
[[new_page|.test_page]]
[[new_page|.:test_page]]
[[..test_page|..test_page]]
[[..:test_page|..:test_page]]
[[.:..:test_page|.:..:test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->otherBacklinkingId);
	    $expectedContent = <<<EOT
[[parent_ns:current_ns:new_page|$this->movedId]]
[[$newId|:$this->movedId]]
[[.current_ns:test_page|.current_ns:test_page]]
[[.:current_ns:test_page|.:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);
	}


	function test_move_page_to_parallel_ns() {
	    global $ID;

	    $newPagename = 'new_page';

	    $opts = array();
	    $opts['page_ns'] = 'page';
	    $opts['ns']   = getNS($ID);
	    $opts['name'] = noNS($ID);
	    $opts['newns'] = 'parent_ns:parallel_ns';
	    $opts['newname'] = $newPagename;
	    $this->movedToId = $opts['newns'].':'.$newPagename;
	    $this->move->move_page($opts);

	    $newId = $opts['newns'].':'.$opts['newname'];
	    $newContent = rawWiki($newId);
	    $expectedContent = <<<EOT
[[parent_ns:current_ns:start|start]]
[[parent_ns:current_ns:parallel_page|parallel_page]]
[[parent_ns:current_ns:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->parentBacklinkingId);
	    $expectedContent = <<<EOT
[[.:parallel_ns:new_page|$this->movedId]]
[[.:parallel_ns:new_page|:$this->movedId]]
[[.:parallel_ns:new_page|.current_ns:test_page]]
[[.:parallel_ns:new_page|.:current_ns:test_page]]
[[.:parallel_ns:new_page|..parent_ns:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->currentNsBacklinkingId);
	    $expectedContent = <<<EOT
[[parent_ns:parallel_ns:new_page|$this->movedId]]
[[$newId|:$this->movedId]]
[[parent_ns:parallel_ns:new_page|..current_ns:test_page]]
[[parent_ns:parallel_ns:new_page|..:current_ns:test_page]]
[[parent_ns:parallel_ns:new_page|test_page]]
[[parent_ns:parallel_ns:new_page|.test_page]]
[[parent_ns:parallel_ns:new_page|.:test_page]]
[[..test_page|..test_page]]
[[..:test_page|..:test_page]]
[[.:..:test_page|.:..:test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->otherBacklinkingId);
	    $expectedContent = <<<EOT
[[parent_ns:parallel_ns:new_page|$this->movedId]]
[[$newId|:$this->movedId]]
[[.current_ns:test_page|.current_ns:test_page]]
[[.:current_ns:test_page|.:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);
	}


	function test_move_page_to_parent_ns() {
	    global $ID;

	    $newPagename = 'new_page';

	    $opts = array();
	    $opts['page_ns'] = 'page';
	    $opts['ns']   = getNS($ID);
	    $opts['name'] = noNS($ID);
	    $opts['newns'] = 'parent_ns';
	    $opts['newname'] = $newPagename;
	    $newId = $opts['newns'].':'.$opts['newname'];
	    $this->movedToId = $opts['newns'].':'.$newPagename;

	    $this->move->move_page($opts);

	    $newContent = rawWiki($newId);
	    $expectedContent = <<<EOT
[[parent_ns:current_ns:start|start]]
[[parent_ns:current_ns:parallel_page|parallel_page]]
[[parent_ns:current_ns:|.:]]
[[parent_ns:current_ns:|..current_ns:]]
[[parent_ns:current_ns:|..:current_ns:]]
[[parent_ns:parallel_ns:|..parallel_ns:]]
[[parent_ns:parallel_ns:|..:parallel_ns:]]
[[:|..:..:]]
[[start|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    // page is moved to same NS as backlinking page (parent_ns)
	    $newContent = rawWiki($this->parentBacklinkingId);
	    $expectedContent = <<<EOT
[[new_page|$this->movedId]]
[[new_page|:$this->movedId]]
[[new_page|.current_ns:test_page]]
[[new_page|.:current_ns:test_page]]
[[new_page|..parent_ns:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->currentNsBacklinkingId);
	    $expectedContent = <<<EOT
[[parent_ns:new_page|$this->movedId]]
[[$newId|:$this->movedId]]
[[parent_ns:new_page|..current_ns:test_page]]
[[parent_ns:new_page|..:current_ns:test_page]]
[[parent_ns:new_page|test_page]]
[[parent_ns:new_page|.test_page]]
[[parent_ns:new_page|.:test_page]]
[[..test_page|..test_page]]
[[..:test_page|..:test_page]]
[[.:..:test_page|.:..:test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);

	    $newContent = rawWiki($this->otherBacklinkingId);
	    $expectedContent = <<<EOT
[[parent_ns:new_page|$this->movedId]]
[[$newId|:$this->movedId]]
[[.current_ns:test_page|.current_ns:test_page]]
[[.:current_ns:test_page|.:current_ns:test_page]]
[[test_page|test_page]]
[[new_page|new_page]]
[[ftp://somewhere.com|ftp://somewhere.com]]
[[http://somewhere.com|http://somewhere.com]]

[[start|start]]
[[parallel_page|parallel_page]]
[[.:|.:]]
[[..current_ns:|..current_ns:]]
[[..:current_ns:|..:current_ns:]]
[[..parallel_ns:|..parallel_ns:]]
[[..:parallel_ns:|..:parallel_ns:]]
[[..:..:|..:..:]]
[[..:..:parent_ns:|..:..:parent_ns:]]
[[parent_ns:new_page|parent_ns:new_page]]
[[parent_ns/new_page|parent_ns/new_page]]
[[/start|/start]]
EOT;
	    $this->assertEquals($expectedContent, $newContent);
	}


	function test_move_ns_in_same_ns() {

	    $newNamespace = 'new_ns';
        $newPagename = '';

	    $opts = array();
	    $opts['page_ns'] = 'ns';
	    $opts['newns'] = 'parent_ns'.':'.$newNamespace;
	    $opts['newname'] = $newPagename;
	    $this->movedToId = $opts['newns'].':'.$newPagename;

	    //$this->move->_pm_move_recursive($opts);

	}

}

