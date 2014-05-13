<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(__DIR__ . '/../helper/plan.php');

/**
 * Test cases for the move plugin
 *
 * @group plugin_move
 * @group plugins
 */
class plugin_move_plan_test extends DokuWikiTest {

    /**
     * Create some page namespace structure
     */
    function setUp() {
        $pages = array(
            'animals:mammals:bear:brownbear',
            'animals:mammals:bear:blackbear',
            'animals:mammals:cute:otter',
            'animals:mammals:cute:cat',
            'animals:mammals:cute:dog',
            'animals:insects:butterfly:fly',
            'animals:insects:butterfly:moth',
            'animals:monkey',
            'humans:programmers:andi',
            'humans:programmers:joe',
            'humans:programmers:john',
            'yeti'
        );
        foreach($pages as $page) {
            saveWikiText($page, $page, 'test setup');
        }

        parent::setUp();
    }

    /**
     * Check that the plan is sorted into the right order
     */
    function test_sorting() {
        $plan = new test_helper_plugin_move_plan();

        $plan->addPageNamespaceMove('animals:mammals:bear', 'animals:mammals:cute:bear');
        $plan->addPageNamespaceMove('humans:programmers', 'animals:mammals:cute:programmers');
        $plan->addPageMove('humans:programmers:andi', 'animals:insects:butterfly:andi');
        $plan->addPageMove('yeti', 'humans:yeti');
        $plan->addPageMove('animals:monkey', 'monkey');

        $sorted = $plan->sortedPlan();

        // the plan is sorted FORWARD (first things first)
        $this->assertEquals(5, count($sorted));
        $this->assertEquals('humans:programmers:andi', $sorted[0]['src']);
        $this->assertEquals('animals:monkey', $sorted[1]['src']);
        $this->assertEquals('yeti', $sorted[2]['src']);
        $this->assertEquals('animals:mammals:bear', $sorted[3]['src']);
        $this->assertEquals('humans:programmers', $sorted[4]['src']);
    }

    /**
     * Move a page out of a namespace and then move the namespace elsewhere
     */
    function test_pageinnamespace() {
        $plan = new test_helper_plugin_move_plan();

        $plan->addPageNamespaceMove('animals:mammals:cute', 'animals:mammals:funny');
        $plan->addPageMove('animals:mammals:cute:otter', 'animals:mammals:otter');

        $plan->commit();
        $list = $plan->getList('pagelist');

        // the files are sorted BACKWARDS (first things last)
        $this->assertEquals(3, count($list));
        $this->assertEquals("animals:mammals:cute:otter\tanimals:mammals:otter", trim($list[2]));
        $this->assertEquals("animals:mammals:cute:cat\tanimals:mammals:funny:cat", trim($list[1]));
        $this->assertEquals("animals:mammals:cute:dog\tanimals:mammals:funny:dog", trim($list[0]));

    }
}

/**
 * Class test_helper_plugin_move_plan
 *
 * gives access to some internal stuff of the class
 */
class test_helper_plugin_move_plan extends helper_plugin_move_plan {

    /**
     * Access the sorted plan
     *
     * @return array
     */
    function sortedPlan() {
        usort($this->plan, array($this, 'planSorter'));
        return $this->plan;
    }

    /**
     * Get the full saved list specified by name
     *
     * @param $name
     * @return array
     */
    function getList($name) {
        return file($this->files[$name]);
    }
}