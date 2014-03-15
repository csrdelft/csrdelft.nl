<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for namespace move functionality of the move plugin
 */
class plugin_move_namespace_move_test extends DokuWikiTest {

    public function setUp() {
        $this->pluginsEnabled[] = 'move';
        parent::setUp();
    }

    public function test_move_wiki_namespace() {
        global $AUTH_ACL;

        $AUTH_ACL[] = "wiki:*\t@ALL\t16";

        idx_addPage('wiki:dokuwiki');
        idx_addPage('wiki:syntax');

        /** @var helper_plugin_move $move  */
        $move = plugin_load('helper', 'move');
        $opts = array(
            'ns' => 'wiki',
            'newns' => 'foo',
            'contenttomove' => 'both'
        );

        $this->assertSame(3, $move->start_namespace_move($opts));
        $this->assertSame(1, $move->continue_namespace_move());
        $this->assertSame(0, $move->continue_namespace_move());

        $this->assertFileExists(wikiFN('foo:dokuwiki'));
        $this->assertFileNotExists(wikiFN('wiki:syntax'));
        $this->assertFileExists(mediaFN('foo:dokuwiki-128.png'));
    }
}
