<?php

require_once dirname(__FILE__).'/../syntax.php';

/**
 * @group plugin_csv
 * @group plugins
 */
class syntax_plugin_csv_test extends DokuWikiTest {

    private $delimiters = array(
        'c' => ',',
        's' => ';',
        't' => "\t"
    );

    private $enclosings = array(
        'q' => '"',
        's' => "'",
    );

    private $escapes = array(
        'q' => '"',
        'b' => '\\'
    );

    function test_files(){
        // run through all the test files
        $files = glob(__DIR__.'/csv/*.csv');
        foreach($files as $file){
            // load test csv and json files
            $csv  = file_get_contents($file);
            $file = basename($file, '.csv');
            $json = file_get_contents(__DIR__.'/json/'.$file.'.json');

            // get delimiter configs form file name
            list($delim, $enc, $esc) =  explode('-', $file);
            $delim = $this->delimiters[$delim];
            $enc = $this->enclosings[$enc];
            $esc = $this->escapes[$esc];

            // test
            $this->assertEquals(json_decode($json, true), $this->csvparse($csv, $delim, $enc, $esc), $file);
        }
    }

    /**
     * Calls the CSV line parser of our plugin and returns the whole array
     *
     * @param string $csvdata
     * @param string $delim
     * @param string $enc
     * @param string $esc
     * @return array
     */
    function csvparse($csvdata, $delim, $enc, $esc){
        $plugin = new syntax_plugin_csv();

        $data = array();

        while($csvdata != '') {
            $line = $plugin->csv_explode_row($csvdata, $delim, '"', '"');
            if($line !== false) array_push($data, $line);
        }

        return $data;
    }
}
