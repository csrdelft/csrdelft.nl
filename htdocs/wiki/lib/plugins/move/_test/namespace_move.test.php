<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for namespace move functionality of the move plugin
 *
 * @group plugin_move
 * @group plugins
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

        /** @var helper_plugin_move_plan $plan  */
        $plan = plugin_load('helper', 'move_plan');

        $this->assertFalse($plan->inProgress());

        $plan->addPageNamespaceMove('wiki', 'foo');
        $plan->addMediaNamespaceMove('wiki', 'foo');

        $plan->commit();

        $this->assertSame(1, $plan->nextStep()); // pages
        $this->assertSame(1, $plan->nextStep()); // media
        $this->assertSame(1, $plan->nextStep()); // missing
        $this->assertSame(1, $plan->nextStep()); // links
        $this->assertSame(1, $plan->nextStep()); // namepaces
        $this->assertSame(0, $plan->nextStep()); // done

        $this->assertFileExists(wikiFN('foo:dokuwiki'));
        $this->assertFileNotExists(wikiFN('wiki:syntax'));
        $this->assertFileExists(mediaFN('foo:dokuwiki-128.png'));
    }

    public function test_move_missing() {
        saveWikiText('oldspace:page', '[[missing]]', 'setup');
        idx_addPage('oldspace:page');

        /** @var helper_plugin_move_plan $plan  */
        $plan = plugin_load('helper', 'move_plan');

        $this->assertFalse($plan->inProgress());

        $plan->addPageNamespaceMove('oldspace', 'newspace');

        $plan->commit();

        $this->assertSame(1, $plan->nextStep()); // pages
        $this->assertSame(1, $plan->nextStep()); // missing
        $this->assertSame(1, $plan->nextStep()); // links
        $this->assertSame(1, $plan->nextStep()); // namepaces
        $this->assertSame(0, $plan->nextStep()); // done

        $this->assertFileExists(wikiFN('newspace:page'));
        $this->assertFileNotExists(wikiFN('oldspace:page'));

        $this->assertEquals('[[missing]]', rawWiki('newspace:page'));
    }
}
